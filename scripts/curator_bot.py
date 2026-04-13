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

    # Lógica Cíclica (Cada ciclo de 3 semanas cambia la fuente)
    # week_num 1 % 3 = 1 (Smashing), 2 % 3 = 2 (Codrops), 3 % 3 = 0 (Product Hunt)
    feeds = {
        1: {"name": "Smashing Magazine", "url": "https://www.smashingmagazine.com/feed/"},
        2: {"name": "Codrops", "url": "https://tympanus.net/codrops/feed/"},
        0: {"name": "Product Hunt", "url": "https://www.producthunt.com/feed"} 
        # Nota: PH feed es general, le pedimos a IA explícitamente "Developer Tools"
    }

    week_num = datetime.date.today().isocalendar()[1]
    cycle_index = week_num % 3
    target_feed = feeds[cycle_index]

    print(f"Semana {week_num} (Index {cycle_index}): Fetching de {target_feed['name']}")

    # Parse RSS
    feed = feedparser.parse(target_feed['url'])
    entries = feed.entries[:10]  # Tomar los 10 más recientes max

    links_payload = ""
    for entry in entries:
        links_payload += f"- Titulo: {entry.title}\n- Link: {entry.link}\n- Extracto: {entry.description[:300]}...\n\n"

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
    Tu objetivo es leer los siguientes artículos/recursos recientes de '{target_feed['name']}' y elegir SOLO UNO que sea el más relevante y útil (por ejemplo: recursos de diseño, CSS avanzado, herramientas de programación {', idealmente Developer Tools' if target_feed['name'] == 'Product Hunt' else ''}).

    Si ninguno es interesante o útil para la audiencia, responde EXACTAMENTE con la palabra "SKIP".

    Si encuentras un buen recurso, debes redactar la respuesta EXACTAMENTE en formato JSON plano (sin marcadores de bloque de código como ```json). El JSON debe contener:
    {{
        "title": "Título corto y conciso (MÁXIMO 40 caracteres)",
        "content_text": "Descripción breve y atractiva (MÁXIMO 70 caracteres)",
        "link": "El enlace original exacto del ítem seleccionado",
        "category": "UNA sola categoría de esta lista: {', '.join(categories_map.keys())}"
    }}

    EJEMPLO de formato correcto:
    {{
        "title": "CSS & HTML Buttons",
        "content_text": "Botones personalizables hechos con puro CSS y HTML, fáciles de implementar",
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
