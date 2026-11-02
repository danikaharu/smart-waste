import os
import shutil
import zipfile
import urllib.request
from pathlib import Path
from PIL import Image
from sklearn.model_selection import train_test_split

BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_DIR = BASE_DIR / "dataset"
RAW_DIR = DATASET_DIR / "raw"
TRASHNET_ZIP = RAW_DIR / "trashnet.zip"
TRASHNET_URL = "https://raw.githubusercontent.com/garythung/trashnet/master/data/dataset-resized.zip"
KAGGLE_ORGANIC_URL_FMT = "https://raw.githubusercontent.com/kurnivan-ny/waste-classification/main/TRAIN/O/O_{}.jpg"

CLASSES = ["organic", "plastic", "paper"]
SPLITS = ["train", "val", "test"]
TARGET_PER_CLASS = 100

def ensure_raw_downloads():
    RAW_DIR.mkdir(parents=True, exist_ok=True)
    if not TRASHNET_ZIP.exists():
        print(f"Downloading TrashNet dataset from {TRASHNET_URL}...")
        urllib.request.urlretrieve(TRASHNET_URL, TRASHNET_ZIP)
        print("TrashNet downloaded successfully.")

def get_organic_images(target_count=100):
    organic_dir = RAW_DIR / "organic"
    organic_dir.mkdir(parents=True, exist_ok=True)
    
    existing = list(organic_dir.glob("*.jpg"))
    if len(existing) >= target_count:
        return existing[:target_count]

    print(f"Fetching {target_count} organic waste images...")
    collected = []
    idx = 1
    while len(collected) < target_count and idx < 1000:
        url = KAGGLE_ORGANIC_URL_FMT.format(idx)
        out_file = organic_dir / f"organic_{idx}.jpg"
        if not out_file.exists():
            try:
                urllib.request.urlretrieve(url, out_file)
                # Verify image integrity
                with Image.open(out_file) as img:
                    img.verify()
                collected.append(out_file)
            except Exception:
                if out_file.exists():
                    out_file.unlink()
        else:
            collected.append(out_file)
        idx += 1
    print(f"Successfully collected {len(collected)} organic images.")
    return collected

def extract_trashnet_images(category, target_count=100):
    dest_dir = RAW_DIR / category
    dest_dir.mkdir(parents=True, exist_ok=True)
    
    existing = list(dest_dir.glob("*.jpg"))
    if len(existing) >= target_count:
        return existing[:target_count]

    with zipfile.ZipFile(TRASHNET_ZIP, 'r') as zf:
        # Find entries matching category
        candidates = [
            f for f in zf.namelist()
            if f"dataset-resized/{category}/" in f and f.endswith(".jpg")
        ]
        print(f"Found {len(candidates)} candidates for {category} in TrashNet.")
        selected = candidates[:target_count]
        extracted = []
        for member in selected:
            fname = Path(member).name
            target_path = dest_dir / fname
            if not target_path.exists():
                with zf.open(member) as source, open(target_path, "wb") as target:
                    shutil.copyfileobj(source, target)
            extracted.append(target_path)
    print(f"Extracted {len(extracted)} {category} images.")
    return extracted

def sanitize_and_save(src_path, dst_path):
    with Image.open(src_path) as img:
        img = img.convert("RGB")
        if max(img.size) > 512:
            img.thumbnail((512, 512), Image.Resampling.LANCZOS)
        img.save(dst_path, "JPEG", quality=90)

def prepare_splits():
    ensure_raw_downloads()
    
    images_by_class = {
        "organic": get_organic_images(TARGET_PER_CLASS),
        "plastic": extract_trashnet_images("plastic", TARGET_PER_CLASS),
        "paper": extract_trashnet_images("paper", TARGET_PER_CLASS)
    }

    # Clean existing splits
    for split in SPLITS:
        for cls in CLASSES:
            folder = DATASET_DIR / split / cls
            if folder.exists():
                shutil.rmtree(folder)
            folder.mkdir(parents=True, exist_ok=True)

    total_count = 0
    for cls, img_list in images_by_class.items():
        # Stratified 70% train, 15% val, 15% test
        train_imgs, temp_imgs = train_test_split(img_list, test_size=0.30, random_state=42)
        val_imgs, test_imgs = train_test_split(temp_imgs, test_size=0.50, random_state=42)
        
        split_map = {
            "train": train_imgs,
            "val": val_imgs,
            "test": test_imgs
        }
        
        for split_name, files in split_map.items():
            for i, src in enumerate(files):
                dst = DATASET_DIR / split_name / cls / f"{cls}_{i+1:04d}.jpg"
                sanitize_and_save(src, dst)
                total_count += 1
                
        print(f"Class '{cls}': Train={len(train_imgs)}, Val={len(val_imgs)}, Test={len(test_imgs)}")

    print(f"\nAll dataset splits prepared! Total images across splits: {total_count}")
    return total_count

if __name__ == "__main__":
    prepare_splits()
