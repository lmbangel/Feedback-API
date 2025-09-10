# middleware/auth.py
import os, jwt
from functools import wraps
from flask import request, jsonify

def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        token = None

        if 'Authorization' in request.headers:
            auth_header = request.headers['Authorization']
            parts = auth_header.split()
            if len(parts) == 2 and parts[0] == "Bearer":
                token = parts[1]

        if not token:
            return jsonify({'error': True, 'message': 'Token is missing!'}), 401

        try:
            decoded = jwt.decode(token, os.getenv('JWT_SECRET'), algorithms=["HS256"])
            request.user = decoded
        except jwt.ExpiredSignatureError:
            return jsonify({'error': True, 'message': 'Token has expired.'}), 401
        except jwt.InvalidTokenError as e:
            return jsonify({'error': True, 'message': f'Invalid token: {str(e)}'}), 401

        return f(*args, **kwargs)
    return decorated
