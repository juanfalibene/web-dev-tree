import os
import sys
import json
import feedparser
import requests
import datetime
from google import genai

import time

def main():
    gemini_key = os.environ.get("GEMINI_API_KEY")
    wp_api_url = os.environ.get("WP_API_URL")
    wp_user = os.environ.get("WP_USERNAME")
    wp_pass = os.environ.get("WP_APP_PASS")

    if not all([gemini_key, wp_api_url, wp_user, wp_pass]):
        print("Missing required environment variables.")
        sys.exit(1)

    # Lógica Cíclica (Cada ciclo de 10 semanas cambia la fuente)
    # 0: Smashing, 1: Codrops, 2: PH, 3: ALA, 4: web.dev, 5: dev.to, 6: fCC, 7: GitHub, 8: Chromium, 9: StackOverflow
    feeds = {
        0: {"name": "Smashing Magazine", "url": "https://www.smashingmagazine.com/feed/", "focus": "artículos de opinión, diseño UX/UI y front-end avanzado"},
        1: {"name": "Codrops", "url": "https://tympanus.net/codrops/feed/", "focus": "innovación front-end, artículos técnicos y demos creativas"},
        2: {"name": "Product Hunt", "url": "https://www.producthunt.com/feed", "focus": "herramientas para desarrolladores (Developer Tools)"},
        3: {"name": "A List Apart", "url": "https://alistapart.com/main/feed/", "focus": "artículos profundos sobre diseño web, accesibilidad y voces de la industria"},
        4: {"name": "web.dev", "url": "https://web.dev/feed.xml", "focus": "estándares web, documentación oficial y blogs de ingeniería"},
        5: {"name": "DEV Community", "url": "https://dev.to/feed/tag/webdev", "focus": "experiencias de la comunidad, guías rápidas y tendencias de desarrollo"},
        6: {"name": "freeCodeCamp News", "url": "https://www.freecodecamp.org/news/rss/", "focus": "cursos completos, tutoriales prácticos y guías paso a paso"},
        7: {"name": "GitHub Blog", "url": "https://github.blog/feed/", "focus": "novedades de herramientas, actualizaciones de Git y cultura open source"},
        8: {"name": "Chromium Blog", "url": "https://blog.chromium.org/feeds/posts/default", "focus": "documentación interna de Chrome, novedades del motor web y APIs experimentales"},
        9: {"name": "StackOverflow Blog", "url": "https://stackoverflow.blog/feed/", "focus": "voces de ingenieros, análisis de la industria y debates de programación"}
    }

    week_num = datetime.date.today().isocalendar()[1]
    cycle_index = week_num % 10
    target_feed = feeds[cycle_index]

    print(f"Semana {week_num} (Index {cycle_index}): Fetching de {target_feed['name']}")

    # Parse RSS
    feed = feedparser.parse(target_feed['url'])
    entries = feed.entries[:10]  # Tomar los 10 más recientes max

    links_payload = ""
    for entry in entries:
        # Algunos feeds usan 'summary' en lugar de 'description'
        desc = entry.get('description', entry.get('summary', ""))[:300]
        links_payload += f"- Titulo: {entry.title}\n- Link: {entry.link}\n- Extracto: {desc}...\n\n"

    # Preparar Prompt para Gemini
    client = genai.Client(api_key=gemini_key)

    # Categorías disponibles en WordPress
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
        "design": 10
    }

    prompt = f"""
    Eres un curador experto en desarrollo web y diseño UX/UI. 
    Tu objetivo es leer los siguientes ítems del feed '{target_feed['name']}' ({target_feed['focus']}) y elegir SOLO UNO que sea el más relevante y útil para una audiencia de desarrolladores profesionales.

    Si ninguno es interesante o realmente aporta valor, responde EXACTAMENTE con la palabra "SKIP".

    Si encuentras un buen recurso, redacta la respuesta EXACTAMENTE en formato JSON plano. El JSON debe contener:
    {{
        "title": "Título corto y conciso (MÁXIMO 40 caracteres)",
        "content_text": "Descripción breve y atractiva (MÁXIMO 70 caracteres)",
        "link": "El enlace original exacto del ítem seleccionado",
        "category": "UNA sola categoría de esta lista: {', '.join(categories_map.keys())}"
    }}

    Prioridad de categorías para esta fuente ({target_feed['name']}):
    - Intenta asignar categorías que este feed cubra bien (ej. freeCodeCamp -> 'courses', web.dev -> 'docs', A List Apart -> 'voices' o 'design').
    - Si el contenido es una herramienta de GitHub Blog o Chromium, usa 'tools' o 'docs'.
    - Si es un artículo de opinión de StackOverflow o Smashing, usa 'voices' o 'articles'.

    EJEMPLO:
    {{
        "title": "CSS & HTML Buttons",
        "content_text": "Botones personalizables hechos con puro CSS y HTML",
        "link": "https://example.com/article",
        "category": "code"
    }}

    Listado de Ítems:
    {links_payload}
    """

    print("Analizando con Gemini...")
    max_retries = 5
    backoff_factor = 2
    initial_delay = 5  # segundos
    response = None

    models_to_try = ["gemini-2.5-flash", "gemini-2.5-flash-lite", "gemini-2.0-flash"]

    for attempt in range(max_retries):
        model_name = models_to_try[attempt % len(models_to_try)]
        try:
            response = client.models.generate_content(
                model=model_name,
                contents=prompt
            )
            break
        except Exception as e:
            if attempt == max_retries - 1:
                print(f"Error persistente tras {max_retries} intentos al llamar a Gemini. Abortando.")
                raise
            delay = initial_delay * (backoff_factor ** attempt)
            print(f"Error al llamar a Gemini con modelo {model_name}: {e}")
            print(f"Reintentando en {delay} segundos (intento {attempt + 1}/{max_retries})...")
            time.sleep(delay)

    output = response.text.strip()

    if output == "SKIP" or "SKIP" in output:
        print("La IA ha decidido saltar esta semana por falta de recursos relevantes.")
        sys.exit(0)

    try:
        # Extraer JSON de forma robusta
        clean_output = output
        if "```" in clean_output:
            lines = clean_output.splitlines()
            code_lines = []
            in_block = False
            for line in lines:
                if line.strip().startswith("```"):
                    in_block = not in_block
                    continue
                if in_block:
                    code_lines.append(line)
            if code_lines:
                clean_output = "\n".join(code_lines)

        start_idx = clean_output.find("{")
        end_idx = clean_output.rfind("}")
        if start_idx != -1 and end_idx != -1:
            clean_output = clean_output[start_idx:end_idx+1]

        data = json.loads(clean_output)
    except json.JSONDecodeError as e:
        print("Error parseando el JSON de Gemini. Output recibido:")
        print(output)
        sys.exit(1)

    # Resolver categoría
    selected_cat = data.get("category", "").lower().strip()
    cat_id = categories_map.get(selected_cat, 9)  # Default: articles
    print(f"Recurso seleccionado: {data.get('title')} | Categoría: {selected_cat} (ID {cat_id})")

    # Publicar en WordPress
    wp_auth = (wp_user, wp_pass)
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
