
from flask import Flask, request, jsonify
import pickle
import numpy as np

# تحميل الأعمدة
with open('columns.pkl', 'rb') as f:
    feature_names = pickle.load(f)

# تحميل الموديلات
models = {
    'log': pickle.load(open('model_log.pkl', 'rb')),
    'mlp': pickle.load(open('model_mlp.pkl', 'rb')),
    'svc': pickle.load(open('model_svc.pkl', 'rb')),
    'nb':  pickle.load(open('model_nb.pkl', 'rb'))
}

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()

    model_key = data.get('model')  # لازم تبعت model = "log" أو "mlp" أو "svc" أو "nb"
    if model_key not in models:
        return jsonify({'error': 'Invalid model key. Use log, mlp, svc, or nb'}), 400

    try:
        features = [data[col] for col in feature_names]
    except KeyError as e:
        return jsonify({'error': f'Missing feature: {str(e)}'}), 400

    input_array = np.array(features).reshape(1, -1)
    prediction = models[model_key].predict(input_array)[0]

    return jsonify({'prediction': int(prediction)})

if __name__ == '__main__':
    app.run(debug=True)
