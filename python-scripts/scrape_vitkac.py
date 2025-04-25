import requests
from bs4 import BeautifulSoup
import pandas as pd
import os
from dotenv import load_dotenv
from concurrent.futures import ThreadPoolExecutor, as_completed

load_dotenv()

def get_category_ids(env_key):
    value = os.getenv(env_key)
    if not value:
        return []
    print(f"Loaded category IDs from environment variable: {value}")
    return [cat_id.strip() for cat_id in value.split(',')]

def fetch_product_images(url):
    """Fetch all image URLs from the product page's photo-module."""
    print(f"Fetching product images from: {url}")
    try:
        response = requests.get(url)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching the product page for images {url}: {e}")
        return []

    soup = BeautifulSoup(response.text, 'html.parser')

    # Find the div containing the images
    photo_module_div = soup.find('div', class_='photo-module')

    if not photo_module_div:
        print(f"No photo-module div found for {url}")
        return []

    # Find all spans inside the photo-module div that contain image links
    image_containers = photo_module_div.find_all('span')

    images = []
    for container in image_containers:
        img_tag = container.find('img')
        if img_tag and img_tag.get('src'):
            images.append(img_tag['src'])  # Add the image URL (from src attribute)

    return images

def fetch_product_description(url):
    """Fetch product description and images from the product page."""
    print(f"Fetching product description from: {url}")
    try:
        response = requests.get(url)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching the product page {url}: {e}")
        return None, []

    soup = BeautifulSoup(response.text, 'html.parser')
    description_div = soup.find('div', class_='elegant-description')
    
    # Fetch images from the product page (first page)
    images = fetch_product_images(url)

    if description_div:
        description = ' '.join(description_div.stripped_strings)
        print(f"Found description for {url}")
        return description, images
    else:
        print(f"No description found on the product page {url}")
        return None, images

def fetch_products_from_page(url, page_number):
    """Fetch products from a category page."""
    print(f"Fetching products from page {page_number}...")
    full_url = url + f"&page={page_number}"

    try:
        response = requests.get(full_url)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching the page {page_number}: {e}")
        return []

    soup = BeautifulSoup(response.text, 'html.parser')
    product_containers = soup.find_all('div', class_='product-media-query')

    if not product_containers:
        print(f"No product containers found on page {page_number}!")
        return []

    print(f"Found {len(product_containers)} products on page {page_number}")
    
    products = []
    # concurrently fetch product descriptions and images
    with ThreadPoolExecutor(max_workers=10) as executor:
        future_to_product = {executor.submit(fetch_product_description, "https://www.vitkac.com" + product.find('a', href=True)['href']): product for product in product_containers}
        
        for future in as_completed(future_to_product):
            product = future_to_product[future]
            try:
                detailed_description, images = future.result()
                name = product.find('h4')
                product_description = product.find('p')  # Description from category page
                price = product.find('label', class_='notranslate')

                if name and product_description and price:
                    products.append({
                        'name': name.get_text(strip=True),
                        'product_description': product_description.get_text(strip=True),  # Original description
                        'detailed_description': detailed_description if detailed_description else 'N/A',  # Detailed description from product page
                        'price': price.get_text(strip=True),
                        'url': "https://www.vitkac.com" + product.find('a', href=True)['href'],
                        'additional_images': images if images else []  # Add all images from the product page
                    })
                else:
                    print(f"Skipping product due to missing fields.")
            except Exception as e:
                print(f"Error processing product: {e}")
    
    return products

def fetch_all_products_for_category(url, category_id):
    """Fetch all products for a given category."""
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
    """Save the products data to CSV."""
    if not products:
        print(f"No products to save for category {category_id}.")
        return

    folder_path = '/var/www/storage/app/private/products'
    os.makedirs(folder_path, exist_ok=True)
    filename = os.path.join(folder_path, f'{section_name}_category_{category_id}.csv')
    df = pd.DataFrame(products)
    df.to_csv(filename, index=False)
    print(f"Saved {len(products)} products to {filename}")

if __name__ == '__main__':
    print("Starting the scraper...")

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

            print(f"Starting to scrape products for category {category_id} in section {section_name}")
            products = fetch_all_products_for_category(category_url, category_id)

            save_to_csv(products, category_id, section_name)

    print("Scraping process completed!")
