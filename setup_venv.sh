echo "Setting up Python Virtual Environment..."
python3 -m ensurepip --upgrade
python3 -m venv /var/www/venv
/var/www/venv/bin/pip install --upgrade pip
/var/www/venv/bin/pip install requests beautifulsoup4 pandas python-dotenv
/var/www/venv/bin/pip install --upgrade "webdriver-manager>=4.0.0"
/var/www/venv/bin/pip install --upgrade "selenium>=4.0.0"
echo "Python environment setup complete!"
