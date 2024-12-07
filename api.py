from flask import Flask, request, jsonify
from tronpy import Tron
from tronpy.keys import PrivateKey
from dotenv import load_dotenv

app = Flask(__name__)

# Tron ağına bağlan
client = Tron()

load_dotenv()
PRIVATE_KEY = os.getenv('PRIVATE_KEY')

@app.route('/create_address', methods=['POST'])
def create_address():
    try:
        # Yeni bir adres oluştur
        private_key = PrivateKey.random()
        address = private_key.address
        return jsonify({
            "status": "success",
            "address": address,
            "private_key": private_key.hex()
        }), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/check_payment', methods=['POST'])
def check_payment():
    try:
        data = request.json
        address = data.get("address")
        expected_amount = data.get("amount")

        # Adres bakiyesini kontrol et
        balance = client.get_account_balance(address)

        if balance >= expected_amount:
            return jsonify({"status": "success", "message": "Payment received"}), 200
        else:
            return jsonify({"status": "pending", "message": "Waiting for payment"}), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/send_funds', methods=['POST'])
def send_funds():
    try:
        data = request.json
        to_address = data.get("to_address")
        amount = data.get("amount")

        # TRX gönder
        txn = (
            client.trx.transfer(owner_address, to_address, int(amount * 1_000_000))
            .build()
            .sign(PrivateKey(bytes.fromhex(PRIVATE_KEY)))
            .broadcast()
        )
        return jsonify({"status": "success", "txn_id": txn["txid"]}), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


if __name__ == '__main__':
    app.run(port=5000, debug=True)
