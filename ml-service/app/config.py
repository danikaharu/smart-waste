from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_PATH = BASE_DIR / "models" / "waste_classifier.keras"

CLASS_MAPPINGS = {
    0: {"name": "Organic Waste", "category": "Organik"},
    1: {"name": "Paper Waste", "category": "Anorganik (Kertas)"},
    2: {"name": "Plastic Waste", "category": "Anorganik (Plastik)"}
}

ALLOWED_CONTENT_TYPES = {
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp"
}
