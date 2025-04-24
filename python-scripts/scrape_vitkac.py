import requests
from bs4 import BeautifulSoup
import pandas as pd
import re
import os
from dotenv import load_dotenv

load_dotenv()

def get_category_ids(env_key):
    value = os.getenv(env_key)
    if not value:
        return []
    return [cat_id.strip() for cat_id in value.split(',')]


def fetch_products_from_page(url, page_number):
    full_url = url + f"&page={page_number}"
    
    try:
        response = requests.get(full_url)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching the page {page_number}: {e}")
        return []

    soup = BeautifulSoup(response.text, 'html.parser')

    products = []

    product_containers = soup.find_all('div', class_='product-media-query')

    if not product_containers:
        print(f"No product containers found on page {page_number}!")
        return []

    for product in product_containers:
        name = product.find('h4')
        description = product.find('p')
        price = product.find('label', class_='notranslate')
        product_url = product.find('a', href=True)
        image_url = product.find('img', class_='first')

        if name and description and price and product_url and image_url:
            products.append({
                'name': name.get_text(strip=True),
                'description': description.get_text(strip=True),
                'price': price.get_text(strip=True),
                'url': "https://www.vitkac.com" + product_url['href'],
                'image_url': image_url['data-src']
            })

    return products


def fetch_all_products_for_category(url, category_id):
    page_number = 1
    all_products = []

    while True:
        print(f"Scraping page {page_number} for category {category_id}...")

        products = fetch_products_from_page(url, page_number)

        if not products:
            print(f"No products found or end of pages reached for category {category_id}.")
            break

        all_products.extend(products)
        page_number += 1

    return all_products


def save_to_csv(products, category_id, section_name):
    if not products:
        print(f"No products to save for category {category_id}.")
        return

    filename = f'/var/www/storage/app/{section_name}_category_{category_id}.csv'

    df = pd.DataFrame(products)
    df.to_csv(filename, index=False)
    print(f"Saved {len(products)} products to {filename}")


if __name__ == '__main__':
    # List of categories with corresponding section names (e.g., women, men, etc.)
    category_data = [
        {"section": "mezczyzni", "category_ids": get_category_ids("SCRAPER_MEZCZYZNI")},
        {"section": "kobiety", "category_ids": get_category_ids("SCRAPER_KOBIETY")},
        {"section": "dzieci", "category_ids": get_category_ids("SCRAPER_DZIECI")},
    ]

    for section_data in category_data:
        section_name = section_data["section"]
        for category_id in section_data["category_ids"]:

            category_url = f"https://www.vitkac.com/pl/sklep/{section_name}?targets=topFilter%2CproductList%2Coffsets_bottom&params%5B0%5D%5Bname%5D=cat%5B{category_id}%5D&params%5B0%5D%5Bvalue%5D=on&main_category="

            products = fetch_all_products_for_category(category_url, category_id)

            save_to_csv(products, category_id, section_name)
