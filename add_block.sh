#!/bin/bash

# Prompt for block name
read -p "Enter block name: " block_name

# Exit if empty
if [ -z "$block_name" ]; then
  echo "No block name provided."
  exit 1
fi

# Convert to lowercase and replace spaces
block_slug=$(echo "$block_name" | tr '[:upper:]' '[:lower:]' | tr ' ' '_')
block_kebab=$(echo "$block_name" | tr '[:upper:]' '[:lower:]' | tr ' ' '-')

# Define file paths
php_file="./blocks/${block_kebab}.php"
blocks_php="./inc/blocks.php"
acf_json_file="./acf-json/group_${block_slug}.json"
block_css_hint="./src/blocks/${block_kebab}.css"

# Exit if block already exists, with specific feedback
if [ -f "$php_file" ]; then
  echo "PHP block file already exists: $php_file"
  exit 1
fi

if [ -f "$acf_json_file" ]; then
  echo "ACF JSON file already exists: $acf_json_file"
  exit 1
fi

# Grab package name from style.css
style_file="./style.css"
package=$(grep "Text Domain:" "$style_file" | sed 's/.*Text Domain:[ ]*//')

# Create PHP template
echo "<?php
/**
 * Block template for ${block_name}.
 *
 * @package ${package}
 */
" > "$php_file"
echo "Created: $php_file"

# No CSS file is created here — the build globs src/blocks/*.css
# automatically. If this block needs custom styles, just add:
echo "If this block needs custom styles, add: $block_css_hint (picked up automatically, no registration needed)"

# Define the marker comment to look for
marker_comment="// INSERT NEW BLOCKS HERE."

# Insert block registration code at the marker comment
block_code=$(cat <<EOF

		acf_register_block_type(
			array(
				'name'            => '${block_slug}',
				'title'           => __( '${block_name}' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/${block_kebab}.php',
				'mode'            => 'edit',
				'supports'        => array(
					'mode'      => false,
					'anchor'    => true,
					'className' => true,
					'align'     => true,
				),
			)
		);
EOF
)

if ! grep -q "$marker_comment" "$blocks_php"; then
  echo "Marker comment not found in $blocks_php. Please add the following comment to the file:"
  echo "$marker_comment"
  exit 1
fi

temp_file=$(mktemp)
if awk -v block_code="$block_code" -v marker="$marker_comment" '
    $0 ~ marker { print; print block_code; next }
    { print }
' "$blocks_php" > "$temp_file"; then
  mv "$temp_file" "$blocks_php"
  echo "Block registered in $blocks_php"
else
  echo "Failed to insert block registration code into $blocks_php"
  rm "$temp_file"
  exit 1
fi

# Create ACF JSON
acf_json_content="{
  \"key\": \"group_${block_slug}\",
  \"title\": \"${block_name}\",
  \"fields\": [
    {
      \"key\": \"field_${block_slug}_title\",
      \"label\": \"${block_name}\",
      \"name\": \"title\",
      \"type\": \"message\"
    }
  ],
  \"location\": [
    [
      {
        \"param\": \"block\",
        \"operator\": \"==\",
        \"value\": \"acf/${block_kebab}\"
      }
    ]
  ],
  \"active\": 1,
  \"description\": \"\"
}"
echo "$acf_json_content" > "$acf_json_file"
echo "Created ACF field group JSON: $acf_json_file"
