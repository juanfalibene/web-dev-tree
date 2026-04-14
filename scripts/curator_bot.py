import os
import sys
import json
import feedparser
import requests
import datetime
from google import genai

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
    response = client.models.generate_content(
        model="gemini-2.5-flash-lite",
        contents=prompt
    )
    output = response.text.strip()

    if output == "SKIP" or "SKIP" in output:
        print("La IA ha decidido saltar esta semana por falta de recursos relevantes.")
        sys.exit(0)

    try:
        # Remover formateos markdown de JSON si Gemini insiste en usarlos
        if output.startswith("```json"):
            output = output.replace("```json", "", 1)
        if output.endswith("```"):
            output = output.rsplit("```", 1)[0]
        output = output.strip()

        data = json.loads(output)
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
    else:
        print(f"Falló la publicación en WP: HTTP {res.status_code}")
        print(res.text)
        sys.exit(1)

if __name__ == "__main__":
    main()
