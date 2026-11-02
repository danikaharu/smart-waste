import pytest
import tensorflow as tf
from pathlib import Path
import sys

# Ensure ml-service is in sys.path
sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

def test_model_architecture():
    from scripts.train import build_waste_classifier
    model = build_waste_classifier(num_classes=3, input_shape=(224, 224, 3))
    assert model.input_shape == (None, 224, 224, 3)
    assert model.output_shape == (None, 3)
    # Check that output activation is softmax
    assert model.layers[-1].activation.__name__ == 'softmax'
