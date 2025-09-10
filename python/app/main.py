from dotenv import load_dotenv
from flask import Flask, jsonify, request
import sqlite3, os, bcrypt, time, jwt, binascii
from middleware.auth import token_required

dotenv_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), '.env')
load_dotenv(dotenv_path=dotenv_path)

app = Flask(__name__)
DB_PATH = os.path.abspath("db.sqlite")

@app.route('/test')
@token_required
def test():
    return jsonify(message=f"Test from Flask in Docker!")

@app.route('/api/v1/login', methods=["POST"])
def login():
    data = request.json
    email = data.get('user') or None
    password = data.get('password') or None

    if email == None or password == None:
        return jsonify({ 'error':True, 'message': 'Email and password required.' }), 400
    
    db = sqlite3.connect(DB_PATH)
    db.row_factory = sqlite3.Row
    cursor = db.cursor()
    q = "SELECT id, email, password_hash FROM users WHERE email = ?"
    cursor.execute(q, (email,))
    user = cursor.fetchone()
    
    if user == None:
        return jsonify({"error": True, "message": f"User {email} does not exist."}), 404
    
    if bcrypt.checkpw(password.encode('utf-8'), user['password_hash'].encode('utf-8')) == False:
        return jsonify({"error": True, "message": "Invalid password."}), 401
    
    data = { 'error' : False, 'sub' : str(user['id']), 'email' : user['email'],'iat' : int(time.time()), 'exp' : int(time.time()) + 3600}
    encoded = jwt.encode(data, os.getenv('JWT_SECRET'), algorithm='HS256')
    data['token'] = encoded
    
    q = "SELECT id, token, `expiry_timestamp` FROM refresh_tokens WHERE user_id = :user_id Limit 1;"
    cursor.execute(q, {'user_id': user['id']})
    user_refresh_token = cursor.fetchone()
    
    refresh_token = binascii.hexlify(os.urandom(64)).decode()
    if user_refresh_token:
        q = "UPDATE refresh_tokens SET token = :token, expiry_timestamp = :expiry_timestamp WHERE user_id = :user_id"
        cursor.execute(q, {
            'token': refresh_token,
            'expiry_timestamp': int(time.time()) + 60 * 60 * 24 * 30,
            'user_id': user['id']
        })
    else:
        q = "INSERT INTO refresh_tokens (user_id, token, expiry_timestamp) VALUES (:user_id, :token, :expiry_timestamp)"
        cursor.execute(q, {
            'user_id': user['id'],
            'token': refresh_token,
            'expiry_timestamp': int(time.time()) + 60 * 60 * 24 * 30 
        })
    db.commit()
    data['refresh_token'] = refresh_token
    db.close()
    
    return jsonify(data)

if __name__ == '__main__': 
    app.run(debug=True, host='0.0.0.0', port=5000)