import requests
from rdflib import Graph, Namespace, Literal, URIRef
from rdflib.namespace import RDF

# -----------------------------
# CONFIGURACIÓ
# -----------------------------
QUERY = " "
MAX_RESULTS = 20
OUTPUT_FILE = r"C:\clubs\books_instances.ttl"

SCHEMA = Namespace("http://schema.org/")
EX = Namespace("http://example.org/")

# -----------------------------
# CREAR GRAFO RDF
# -----------------------------
g = Graph()
g.bind("schema", SCHEMA)
g.bind("ex", EX)

# -----------------------------
# CONSULTAR GOOGLE BOOKS API
# -----------------------------
url = "https://www.googleapis.com/books/v1/volumes"
params = {"q": QUERY, "maxResults": MAX_RESULTS}
response = requests.get(url, params=params)
data = response.json()

author_counter = 1
author_uri_map = {}
book_counter = 1
rating_counter = 1

# -----------------------------
# CREAR INSTÀNCIES RDF
# -----------------------------
for item in data.get("items", []):
    volume = item.get("volumeInfo", {})

    title = volume.get("title")
    if not title:
        continue

    book_uri = URIRef(EX + f"book{book_counter}")
    g.add((book_uri, RDF.type, SCHEMA.Book))
    g.add((book_uri, SCHEMA.name, Literal(title)))

    # Autor (només primer autor)
    authors = volume.get("authors", [])
    if authors:
        author_name = authors[0]
        if author_name not in author_uri_map:
            author_uri = URIRef(EX + f"author{author_counter}")
            g.add((author_uri, RDF.type, SCHEMA.Person))
            g.add((author_uri, SCHEMA.name, Literal(author_name)))
            author_uri_map[author_name] = author_uri
            author_counter += 1
        g.add((book_uri, SCHEMA.author, author_uri_map[author_name]))
        g.add((book_uri, SCHEMA['authorName'], Literal(author_name)))

    # ISBN (només ISBN_13)
    for identifier in volume.get("industryIdentifiers", []):
        if identifier.get("type") == "ISBN_13":
            g.add((book_uri, SCHEMA.isbn, Literal(identifier.get("identifier"))))
            break

    # Data publicació
    published_date = volume.get("publishedDate", "unknown")
    g.add((book_uri, SCHEMA.datePublished, Literal(published_date)))

    # Rating (si existeix)
    rating_value = volume.get("averageRating")
    if rating_value:
        rating_uri = URIRef(EX + f"rating{rating_counter}")
        g.add((rating_uri, RDF.type, SCHEMA.AggregateRating))
        g.add((rating_uri, SCHEMA.ratingValue, Literal(rating_value)))
        g.add((book_uri, SCHEMA.aggregateRating, rating_uri))
        rating_counter += 1

        

    book_counter += 1

# -----------------------------
# GUARDAR RESULTAT
# -----------------------------
g.serialize(destination=OUTPUT_FILE, format="turtle")
print(f"Instàncies RDF de llibres amb rating generades correctament en {OUTPUT_FILE}")