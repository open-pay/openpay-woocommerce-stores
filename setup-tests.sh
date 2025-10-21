#!/bin/bash

# --- 1. Ubicación permanente de carpetas ---
# '~/wp-test-environment' se expandirá a '/Users/tu-usuario/wp-test-environment'
TEST_ENV_DIR=~/wp-test-environment
WP_CORE_DIR="$TEST_ENV_DIR/wordpress"
WP_TESTS_DIR="$TEST_ENV_DIR/wordpress-tests-lib"

# --- Acepta parámetros y muestra ayuda si faltan ---
if [ "$#" -lt 4 ]; then
    echo "Uso: $0 <db_name> <db_user> <db_pass> <db_host>"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=$4

echo "🚀 Configurando el entorno de pruebas permanente en '$TEST_ENV_DIR'..."

# --- 2. Limpieza y Creación de la Carpeta Permanente ---
echo "Limpiando y creando directorios..."
rm -rf "$TEST_ENV_DIR"
mkdir -p "$TEST_ENV_DIR"

# --- 3. Descarga (ahora usando la nueva variable) ---
echo "Descargando WordPress Core y Suite de Pruebas..."
curl -L https://wordpress.org/latest.tar.gz | tar -C "$TEST_ENV_DIR" -xzf -
curl -L https://github.com/WordPress/wordpress-develop/archive/refs/heads/trunk.zip -o "$TEST_ENV_DIR/wp-tests.zip"
unzip -q "$TEST_ENV_DIR/wp-tests.zip" -d "$TEST_ENV_DIR"
mv "$TEST_ENV_DIR/wordpress-develop-trunk" "$WP_TESTS_DIR"

# --- 4. Configuración del archivo wp-tests-config.php (ahora usando la nueva variable) ---
echo "Configurando wp-tests-config.php..."
cp "$WP_TESTS_DIR/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i '' "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i '' "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i '' "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i '' "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i '' "s/localhost/$DB_HOST/" "$WP_TESTS_DIR/wp-tests-config.php"

# --- 5. Instalación de WooCommerce (ahora usando la nueva variable) ---
echo "Descargando WooCommerce..."
curl -L https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip -o "$TEST_ENV_DIR/woocommerce.zip"
unzip -q "$TEST_ENV_DIR/woocommerce.zip" -d "$WP_CORE_DIR/wp-content/plugins/"

echo "✅ ¡Entorno de pruebas permanente listo!"