#!/bin/bash

# Get list of block files
blocks_dir="./blocks"
if [ ! -d "$blocks_dir" ]; then
  echo "Blocks directory not found: $blocks_dir"
  exit 1
fi

# Create array of block files
mapfile -t block_files < <(ls "$blocks_dir"/*.php 2>/dev/null | xargs -n 1 basename | sort)

if [ ${#block_files[@]} -eq 0 ]; then
  echo "No blocks found in $blocks_dir"
  exit 1
fi

# Display numbered list
echo ""
echo "Available blocks:"
echo ""
for i in "${!block_files[@]}"; do
  block_name="${block_files[$i]%.php}"
  printf "%2d) %s\n" $((i+1)) "$block_name"
done
echo ""

# Prompt for selection
read -p "Enter block number to remove (or 'q' to quit): " selection

# Check if user wants to quit
if [ "$selection" == "q" ] || [ "$selection" == "Q" ]; then
  echo "Cancelled."
  exit 0
fi

# Validate selection
if ! [[ "$selection" =~ ^[0-9]+$ ]] || [ "$selection" -lt 1 ] || [ "$selection" -gt ${#block_files[@]} ]; then
  echo "Invalid selection."
  exit 1
fi

# Get selected block
selected_index=$((selection-1))
block_kebab="${block_files[$selected_index]%.php}"
block_slug=$(echo "$block_kebab" | tr '-' '_')

# Define file paths
php_file="./blocks/${block_kebab}.php"
css_file="./src/blocks/${block_kebab}.css"
blocks_php="./inc/blocks.php"
acf_json_file="./acf-json/group_${block_slug}.json"

# Confirm before deletion
echo ""
echo "This will remove the following:"
echo "  - PHP file: $php_file"
echo "  - CSS file (if present): $css_file"
echo "  - Registration from: $blocks_php"
echo "  - ACF JSON: $acf_json_file"
echo ""
read -p "Are you sure you want to remove this block? (y/n): " confirm

if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
  echo "Cancelled."
  exit 0
fi

# Remove PHP file
if [ -f "$php_file" ]; then
  rm "$php_file"
  echo "Removed: $php_file"
else
  echo "Not found: $php_file"
fi

# Remove CSS file, if this block had one — not all blocks do (add_block.sh
# doesn't create one by default; src/blocks/*.css is only ever present if
# someone added custom styles for this specific block).
if [ -f "$css_file" ]; then
  rm "$css_file"
  echo "Removed: $css_file"
else
  echo "No CSS file to remove (block had none): $css_file"
fi

# Remove block registration from blocks.php
if [ -f "$blocks_php" ]; then
  # Create a temporary file
  temp_file=$(mktemp)
  
  # Use awk to remove the block registration section
  awk -v block_slug="${block_slug}" '
    BEGIN { in_block = 0; block_started = 0 }
    
    /acf_register_block_type\(/ {
      block_started = 1
      block_buffer = $0 "\n"
      next
    }
    
    block_started {
      block_buffer = block_buffer $0 "\n"
      
      # Check if this line contains the block name we want to remove
      if ($0 ~ "'"'"'name'"'"'.*=>.*'"'"'" block_slug "'"'"'") {
        in_block = 1
      }
      
      # Found the end of the block registration
      if ($0 ~ /\);$/) {
        block_started = 0
        if (!in_block) {
          # This is not the block we want to remove, so print it
          printf "%s", block_buffer
        }
        in_block = 0
        block_buffer = ""
        next
      }
      next
    }
    
    { print }
  ' "$blocks_php" > "$temp_file"
  
  if [ -s "$temp_file" ]; then
    mv "$temp_file" "$blocks_php"
    chmod 664 "$blocks_php"
    chgrp www-data "$blocks_php" 2>/dev/null || true
    echo "Removed registration from: $blocks_php"
  else
    echo "Failed to remove registration from: $blocks_php"
    rm "$temp_file"
  fi
else
  echo "Not found: $blocks_php"
fi

# Remove ACF JSON file
if [ -f "$acf_json_file" ]; then
  rm "$acf_json_file"
  echo "Removed: $acf_json_file"
else
  echo "Not found: $acf_json_file"
fi

echo ""
echo "Block removal complete!"
echo "Note: You may need to sync ACF field groups in WordPress admin."