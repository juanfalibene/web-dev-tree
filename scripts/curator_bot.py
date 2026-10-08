import os
import sys
import json
import feedparser
import requests
import datetime
from google import genai

import re
import time

USER_AGENT = "Mozilla/5.0 (compatible; WebDevTreeCuratorBot/1.0; +https://github.com/)"

# URLs alternativas por si el feed principal bloquea a los runners de GitHub (HTTP 403)
FALLBACK_FEEDS = {
    "https://tympanus.net/codrops/feed/": ["https://feeds.feedburner.com/tympanus"],
}

def fetch_feed_entries(url):
    """
    Descarga el feed con un User-Agent propio y prueba URLs alternativas si falla.
    Devuelve la lista de entradas (vacía si ningún origen funcionó).
    """
    for candidate in [url] + FALLBACK_FEEDS.get(url, []):
        try:
            res = requests.get(candidate, headers={"User-Agent": USER_AGENT}, timeout=20)
            if res.status_code != 200:
                print(f"Aviso: {candidate} respondió HTTP {res.status_code}")
                continue
            parsed = feedparser.parse(res.content)
            if parsed.entries:
                return parsed.entries
            print(f"Aviso: {candidate} no devolvió entradas.")
        except Exception as e:
            print(f"Aviso: error descargando {candidate}: {e}")
    return []

def extract_resource(output, valid_links):
    """
    Extrae el primer objeto JSON válido (con title y link) de la respuesta del modelo,
    ignorando texto extra, bloques ``` y arrays. Si hay lista de enlaces válidos,
    exige que el link pertenezca al feed (evita enlaces alucinados).
    """
    decoder = json.JSONDecoder()
    for match in re.finditer(r"[{\[]", output):
        try:
            obj, _ = decoder.raw_decode(output[match.start():])
        except json.JSONDecodeError:
            continue
        candidates = obj if isinstance(obj, list) else [obj]
        for cand in candidates:
            if not isinstance(cand, dict):
                continue
            if not all(k in cand for k in ("title", "content_text", "link")):
                continue
            if valid_links and cand["link"] not in valid_links:
                continue
            return cand
    return None

def fetch_wp_categories(wp_api_url, wp_auth):
    """
    Intenta obtener las categorías de la taxonomía category_resource desde la API REST de WordPress.
    Devuelve un diccionario {slug: term_id}.
    """
    try:
        parts = wp_api_url.split('/wp/v2/')
        if len(parts) > 1:
            cat_url = parts[0] + '/wp/v2/category_resource?per_page=100'
            res = requests.get(cat_url, auth=wp_auth, timeout=8)
            if res.status_code == 200:
                categories = res.json()
                mapped = {}
                for cat in categories:
                    slug = cat.get('slug', '').lower()
                    cat_id = cat.get('id')
                    if slug and cat_id:
                        mapped[slug] = cat_id
                if mapped:
                    print(f"Categorías detectadas en WordPress API: {list(mapped.keys())}")
                    return mapped
    except Exception as e:
        print(f"Aviso: No se pudo obtener categorías dinámicas de WP ({e}). Usando mapa estático.")
    return {}

def main():
    gemini_key = os.environ.get("GEMINI_API_KEY")
    wp_api_url = os.environ.get("WP_API_URL")
    wp_user = os.environ.get("WP_USERNAME")
    wp_pass = os.environ.get("WP_APP_PASS")

    if not all([gemini_key, wp_api_url, wp_user, wp_pass]):
        print("Missing required environment variables.")
        sys.exit(1)

    wp_auth = (wp_user, wp_pass)
    dynamic_cats = fetch_wp_categories(wp_api_url, wp_auth)

    # Categorías disponibles por defecto en WordPress
    categories_map = {
        "inspiration": 3,
        "tools": 4,
        "courses": 5,
        "voices": 13,
        "tutorials": 6,
        "docs": 7,
        "code": 11,
        "blogs": 12,
        "articles": 9,
        "design": 10,
        "ai": 14
    }

    # Sobrescribir con los IDs reales de WordPress si existen
    for slug, term_id in dynamic_cats.items():
        categories_map[slug] = term_id
        if slug in ["ai", "ia", "ai-tools", "inteligencia-artificial"]:
            categories_map["ai"] = term_id

    # Lógica Cíclica (Cada ciclo de 10 semanas cambia la fuente)
    feeds = {
        0: {"name": "Smashing Magazine", "url": "https://www.smashingmagazine.com/feed/", "focus": "consejos prácticos de UX/UI, trucos de CSS y guías accesibles de diseño front-end"},
        1: {"name": "Codrops", "url": "https://tympanus.net/codrops/feed/", "focus": "demos creativas, efectos CSS/JS interactivos e inspiración visual fácil de implementar"},
        2: {"name": "Product Hunt", "url": "https://www.producthunt.com/feed", "focus": "nuevas herramientas para desarrolladores, utilidades de productividad y aplicaciones de IA"},
        3: {"name": "A List Apart", "url": "https://alistapart.com/main/feed/", "focus": "artículos amigables sobre diseño web, accesibilidad y buenas prácticas de la industria"},
        4: {"name": "web.dev", "url": "https://web.dev/feed.xml", "focus": "guías prácticas de rendimiento web, mejores prácticas de HTML/CSS y componentes modernos"},
        5: {"name": "DEV Community", "url": "https://dev.to/feed/tag/webdev", "focus": "tutoriales sencillos de la comunidad, trucos rápidos de código e integraciones de IA"},
        6: {"name": "freeCodeCamp News", "url": "https://www.freecodecamp.org/news/rss/", "focus": "tutoriales paso a paso para principiantes e intermedios, guías de aprendizaje y proyectos"},
        7: {"name": "GitHub Blog", "url": "https://github.blog/feed/", "focus": "trucos de Git, novedades de GitHub Copilot / IA y consejos prácticos para desarrolladores"},
        8: {"name": "Chromium Blog", "url": "https://blog.chromium.org/feeds/posts/default", "focus": "nuevas funcionalidades útiles de navegadores, APIs web modernas y herramientas de DevTools"},
        9: {"name": "StackOverflow Blog", "url": "https://stackoverflow.blog/feed/", "focus": "reflexiones cercanas sobre la carrera dev, encuestas de desarrollo e IA en la programación"}
    }

    week_num = datetime.date.today().isocalendar()[1]
    cycle_index = week_num % 10
    target_feed = feeds[cycle_index]

    print(f"Semana {week_num} (Index {cycle_index}): Fetching de {target_feed['name']}")

    # Parse RSS
    entries = fetch_feed_entries(target_feed['url'])[:10]  # Tomar los 10 más recientes max
    if not entries:
        print(f"Error: no se pudieron obtener entradas del feed de {target_feed['name']}. Abortando para evitar contenido inventado.")
        sys.exit(1)
    valid_links = {entry.link for entry in entries}

    links_payload = ""
    for entry in entries:
        desc = entry.get('description', entry.get('summary', ""))[:300]
        links_payload += f"- Titulo: {entry.title}\n- Link: {entry.link}\n- Extracto: {desc}...\n\n"

    # Preparar Prompt para Gemini
    client = genai.Client(api_key=gemini_key)

    prompt = f"""
    Eres un curador amigable, didáctico y cercano para una comunidad de desarrolladores web y diseñadores UX/UI.
    Tu objetivo es seleccionar UN SOLO recurso del feed '{target_feed['name']}' ({target_feed['focus']}) que sea **práctico, fácil de digerir y verdaderamente útil** para un público de nivel principiante e intermedio.

    REGLAS DE SELECCIÓN Y NIVEL (FILTRO DE DIFICULTAD):
    1. EVITA: Artículos excesivamente complejos, especificaciones técnicas áridas, papers de investigación, o actualizaciones de versión de bajo nivel que solo interesen al 1% de ingenieros senior.
    2. PRIORIZA: Tutoriales paso a paso, trucos visuales de CSS/JS, herramientas que ahorren tiempo (especialmente con IA), componentes interactivos, listas de recursos y artículos con tono explicativo.
    3. Si NINGUNO de los ítems es accesible, interesante o útil, responde EXACTAMENTE con la palabra "SKIP".

    HUMANIZACIÓN DEL TONO Y CONTENIDO:
    - Redacta el título y la descripción en español claro, directo y amigable (evita traducciones robóticas o literales).
    - Título: Atractivo y conciso (MÁXIMO 40 caracteres). Ej: "Crea botones animados en CSS", "5 herramientas de IA para devs", "Guía rápida de Flexbox".
    - Descripción (content_text): Explicación entusiasta y breve del beneficio práctico (MÁXIMO 70 caracteres). Ej: "Aprende a diseñar componentes limpios y accesibles en 5 minutos."

    CATEGORÍAS Y REGLA DE IA:
    - Si el ítem trata sobre Inteligencia Artificial (Copilot, ChatGPT, Claude, generadores de código/UI, herramientas con IA o APIs de LLM), asígnale OBLIGATORIAMENTE la categoría "ai".
    - De lo contrario, asigna UNA categoría de la lista de disponibles: {', '.join(categories_map.keys())}.

    FORMATO DE RESPUESTA:
    Responde EXACTAMENTE en formato JSON plano:
    {{
        "title": "Título amigable (máx 40 chars)",
        "content_text": "Descripción útil (máx 70 chars)",
        "link": "El enlace original exacto del ítem seleccionado",
        "category": "UNA sola categoría de la lista"
    }}

    Listado de Ítems:
    {links_payload}
    """

    print("Analizando con Gemini...")
    max_retries = 5
    backoff_factor = 2
    initial_delay = 5  # segundos
    response = None

    # Se puede forzar un modelo con la variable de entorno GEMINI_MODEL (se prueba primero)
    models_to_try = ["gemini-3.8-flash", "gemini-2.5-flash-lite", "gemini-2.0-flash", "gemini-2.5-flash"]
    if os.environ.get("GEMINI_MODEL"):
        models_to_try.insert(0, os.environ["GEMINI_MODEL"])

    attempt = 0
    model_idx = 0
    while response is None:
        if model_idx >= len(models_to_try):
            print("Error: ningún modelo de Gemini disponible. Abortando.")
            sys.exit(1)
        model_name = models_to_try[model_idx]
        try:
            response = client.models.generate_content(
                model=model_name,
                contents=prompt
            )
        except Exception as e:
            print(f"Error al llamar a Gemini con modelo {model_name}: {e}")
            msg = str(e)
            # Modelo retirado/no existente: pasar al siguiente sin esperar
            if "404" in msg or "NOT_FOUND" in msg:
                model_idx += 1
                continue
            attempt += 1
            if attempt >= max_retries:
                print(f"Error persistente tras {max_retries} intentos al llamar a Gemini. Abortando.")
                raise
            delay = initial_delay * (backoff_factor ** (attempt - 1))
            print(f"Reintentando en {delay} segundos (intento {attempt}/{max_retries})...")
            time.sleep(delay)

    output = response.text.strip()

    if output.strip("`*\"' \n") == "SKIP":
        print("La IA ha decidido saltar esta semana por falta de recursos relevantes.")
        sys.exit(0)

    data = extract_resource(output, valid_links)
    if data is None:
        print("Error: no se encontró un recurso JSON válido (con enlace del feed) en la respuesta de Gemini. Output recibido:")
        print(output)
        sys.exit(1)

    # Resolver categoría
    selected_cat = data.get("category", "").lower().strip()
    cat_id = categories_map.get(selected_cat, categories_map.get("articles", 9))
    print(f"Recurso seleccionado: {data.get('title')} | Categoría: {selected_cat} (ID {cat_id})")

    # Publicar en WordPress
    wp_data = {
        "title": data["title"],
        "excerpt": data["content_text"],
        "status": "publish",
        "category_resource": [cat_id],
        "meta": {
            "_external_link": data["link"]
        }
    }

    print("Enviando POST a WordPress REST API...")
    res = requests.post(f"{wp_api_url.rstrip('/')}", json=wp_data, auth=wp_auth)

    if res.status_code in [200, 201]:
        print("Post publicado exitosamente!")
        print(f"Link: {data['link']}")

        # Guardar en histórico local para registro y mantener actividad en el repo
        try:
            history_path = os.path.join(os.path.dirname(__file__), "curated_history.json")
            history = []
            if os.path.exists(history_path):
                with open(history_path, "r", encoding="utf-8") as f:
                    try:
                        history = json.load(f)
                    except json.JSONDecodeError:
                        history = []

            history.append({
                "date": datetime.datetime.now(datetime.timezone.utc).isoformat(),
                "week": week_num,
                "source": target_feed['name'],
                "title": data["title"],
                "content_text": data["content_text"],
                "link": data["link"],
                "category": selected_cat,
                "category_id": cat_id
            })

            # Mantener los últimos 100 registros
            if len(history) > 100:
                history = history[-100:]

            with open(history_path, "w", encoding="utf-8") as f:
                json.dump(history, f, indent=2, ensure_ascii=False)
            print("Registro guardado exitosamente en scripts/curated_history.json")
        except Exception as e:
            print(f"Aviso: No se pudo guardar en curated_history.json: {e}")
    else:
        print(f"Falló la publicación en WP: HTTP {res.status_code}")
        print(res.text)
        sys.exit(1)

if __name__ == "__main__":
    main()

