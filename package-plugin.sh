#!/bin/bash

# Koolboks Chat WordPress Plugin Packager
# This script creates a clean, ready-to-upload plugin zip file

echo "🚀 Packaging Koolboks Chat WordPress Plugin..."

# Set the plugin directory name
PLUGIN_DIR="wordpress-plugin"
PLUGIN_NAME="koolboks-chat"
OUTPUT_DIR="dist"
ZIP_NAME="koolboks-chat-plugin.zip"

# Create dist directory if it doesn't exist
mkdir -p "$OUTPUT_DIR"

# Remove old zip if exists
if [ -f "$OUTPUT_DIR/$ZIP_NAME" ]; then
    echo "🗑️  Removing old package..."
    rm "$OUTPUT_DIR/$ZIP_NAME"
fi

# Create temporary directory with proper plugin name
TEMP_DIR=$(mktemp -d)
cp -R "$PLUGIN_DIR" "$TEMP_DIR/$PLUGIN_NAME"

# Remove unnecessary files from temp directory
echo "🧹 Cleaning up unnecessary files..."
find "$TEMP_DIR/$PLUGIN_NAME" -name ".DS_Store" -delete
find "$TEMP_DIR/$PLUGIN_NAME" -name "*.log" -delete
find "$TEMP_DIR/$PLUGIN_NAME" -name "__pycache__" -type d -exec rm -rf {} + 2>/dev/null || true

# Create the zip file
echo "📦 Creating zip file..."
cd "$TEMP_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_NAME" -q

# Move to output directory
mv "$ZIP_NAME" "$OLDPWD/$OUTPUT_DIR/"

# Cleanup
cd "$OLDPWD"
rm -rf "$TEMP_DIR"

echo "✅ Package created successfully!"
echo "📍 Location: $OUTPUT_DIR/$ZIP_NAME"
echo ""
echo "Next steps:"
echo "1. Go to your WordPress admin panel"
echo "2. Navigate to Plugins → Add New → Upload Plugin"
echo "3. Upload the file: $OUTPUT_DIR/$ZIP_NAME"
echo "4. Activate the plugin"
echo "5. Configure settings in Koolboks Chat menu"
echo ""
echo "🎉 Done!"
