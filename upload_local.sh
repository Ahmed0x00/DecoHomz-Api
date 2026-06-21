#!/bin/bash
# Local Product Uploader Script for DecoHomz
# Bypasses "413 Content Too Large" by uploading images sequentially.
set -e

echo "Logging in to local server at port 8081..."
LOGIN_RESPONSE=$(curl -s -X POST http://127.0.0.1:8081/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@decohomz.com","password":"password"}')

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token')

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
  echo "Error logging in:"
  echo "$LOGIN_RESPONSE"
  exit 1
fi

echo "Token successfully obtained."

echo "Creating product Koa Boucle Loveseat on local DB..."
CREATE_RESPONSE=$(curl -s -X POST http://127.0.0.1:8081/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "category_id=6" \
  -F "name=Koa Boucle Loveseat" \
  -F "description=Defined by its soft, rounded silhouette and warm timber foundation, the Koa Boucle Loveseat brings an organic modern presence to the living room. Wrapped in a heavily textured boucle, the two-seater sofa features deep cushioning and sheltering, cylindrical arms that curve around to form a cozy, protective backrest. The standout feature is the sculptural solid wood base, which curves upward to cradle the plush upholstered body. Resting low to the ground on a concealed plinth, this loveseat offers a perfect balance of grounding weight and light, tactile comfort." \
  -F "price=27999" \
  -F "old_price=32999" \
  -F "material=Solid Pine & Engineered Wood" \
  -F "upholstery=Premium Textured Boucle" \
  -F "dimensions=165 cm W x 75 cm D x 90 cm H" \
  -F "weight=38 kg" \
  -F "stars=5" \
  -F "stock=75" \
  -F 'specifications_json={"Dimensions": {"Overall Dimensions": "165 cm W x 75 cm D x 90 cm H", "Seat Height": "40 cm", "Weight": "38 kg"}, "Materials": {"Frame": "Solid pine and engineered wood", "Upholstery": "Premium textured boucle", "Base": "Solid oak veneer with a natural finish"}}' \
  -F 'colors_json=[{"name":"White","hex_code":"#FFFFFF","stock":15},{"name":"Blue","hex_code":"#3B82F6","stock":15},{"name":"Green","hex_code":"#10B981","stock":15},{"name":"Mustard","hex_code":"#EAB308","stock":15},{"name":"Red","hex_code":"#EF4444","stock":15}]')

PRODUCT_ID=$(echo "$CREATE_RESPONSE" | jq -r '.product.id')

if [ "$PRODUCT_ID" == "null" ] || [ -z "$PRODUCT_ID" ]; then
  echo "Error creating product:"
  echo "$CREATE_RESPONSE"
  exit 1
fi

echo "Product created successfully with ID: $PRODUCT_ID"

echo "Fetching color mappings..."
COLORS_RESPONSE=$(curl -s -X GET http://127.0.0.1:8081/api/admin/products/$PRODUCT_ID/colors \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

WHITE_ID=$(echo "$COLORS_RESPONSE" | jq -r '.colors[] | select(.name=="White") | .id')
BLUE_ID=$(echo "$COLORS_RESPONSE" | jq -r '.colors[] | select(.name=="Blue") | .id')
GREEN_ID=$(echo "$COLORS_RESPONSE" | jq -r '.colors[] | select(.name=="Green") | .id')
MUSTARD_ID=$(echo "$COLORS_RESPONSE" | jq -r '.colors[] | select(.name=="Mustard") | .id')
RED_ID=$(echo "$COLORS_RESPONSE" | jq -r '.colors[] | select(.name=="Red") | .id')

IMG_DIR="$(pwd)/Ready-Pictures-Of-Products/Sofa/Koa-Boucle-Loveseat"

upload_image() {
  local file=$1
  local color_id=$2
  local is_primary=$3
  
  echo "Uploading image: $file..."
  curl -s -X POST http://127.0.0.1:8081/api/admin/products/$PRODUCT_ID/images \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json" \
    -F "image=@$IMG_DIR/$file" \
    -F "product_color_id=$color_id" \
    -F "is_primary=$is_primary" \
    -F "alt_text=Koa Boucle Loveseat" \
    | jq -c '{message: .message}'
}

# Upload White images
upload_image "White.png" "$WHITE_ID" "1"
upload_image "White_2.png" "$WHITE_ID" "0"
upload_image "White_3.png" "$WHITE_ID" "0"
upload_image "White_4.png" "$WHITE_ID" "0"
upload_image "White_5.png" "$WHITE_ID" "0"

# Upload Dimensions (no color id)
upload_image "White_Dimensions.png" "" "0"
upload_image "White_Dimensions_2.png" "" "0"

# Upload Blue images
upload_image "Blue.png" "$BLUE_ID" "0"
upload_image "Blue_2.png" "$BLUE_ID" "0"
upload_image "Blue_3.png" "$BLUE_ID" "0"

# Upload Green images
upload_image "Green.png" "$GREEN_ID" "0"
upload_image "Green_2.png" "$GREEN_ID" "0"
upload_image "Green_3.png" "$GREEN_ID" "0"

# Upload Mustard images
upload_image "Mustard.png" "$MUSTARD_ID" "0"
upload_image "Mustard_2.png" "$MUSTARD_ID" "0"
upload_image "Mustard_3.png" "$MUSTARD_ID" "0"

# Upload Red images
upload_image "Red.png" "$RED_ID" "0"
upload_image "Red_2.png" "$RED_ID" "0"
upload_image "Red_3.png" "$RED_ID" "0"

echo "Product upload fully completed locally!"
