echo "Setting up Python Virtual Environment..."
python3 -m venv /var/www/venv
/var/www/venv/bin/pip install --upgrade pip
/var/www/venv/bin/pip install requests beautifulsoup4 pandas python-dotenv
echo "Python environment setup complete!"
