import json
from pathlib import Path

REPORTS_DIR = Path(__file__).resolve().parent.parent / "reports"

def test_evaluation_artifacts_exist():
    assert (REPORTS_DIR / "confusion_matrix.png").exists(), "confusion_matrix.png missing"
    assert (REPORTS_DIR / "classification_report.txt").exists(), "classification_report.txt missing"
    assert (REPORTS_DIR / "metrics.json").exists(), "metrics.json missing"

def test_accuracy_meets_threshold():
    metrics_path = REPORTS_DIR / "metrics.json"
    with open(metrics_path, "r") as f:
        data = json.load(f)
    accuracy = data.get("test_accuracy", 0.0)
    assert accuracy >= 0.80, f"Test accuracy {accuracy*100:.2f}% is below the required 80% threshold"
