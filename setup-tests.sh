#!/bin/bash
echo "🚀 Recreando el entorno de pruebas de WordPress..."

# 1. Limpieza
echo "Limpiando directorios temporales..."
rm -rf /tmp/wordpress
rm -rf /tmp/wordpress-tests-lib

# 2. Descarga de WordPress y Suite de Pruebas
echo "Descargando WordPress Core..."
curl -L https://wordpress.org/latest.tar.gz | tar -C /tmp/ -xzf -
echo "Descargando Suite de Pruebas..."
curl -L https://github.com/WordPress/wordpress-develop/archive/refs/heads/trunk.zip -o /tmp/wp-tests.zip
unzip -q /tmp/wp-tests.zip -d /tmp/
mv /tmp/wordpress-develop-trunk /tmp/wordpress-tests-lib

# 3. Configuración de la Base de Datos
echo "Creando base de datos de prueba..."
mysql -u root -proot -h localhost:8889 -e "DROP DATABASE IF EXISTS wordpress_test_db; CREATE DATABASE wordpress_test_db;"

# 4. Configuración del archivo wp-tests-config.php
echo "Configurando wp-tests-config.php..."
cp /tmp/wordpress-tests-lib/wp-tests-config-sample.php /tmp/wordpress-tests-lib/wp-tests-config.php
sed -i '' "s:dirname( __FILE__ ) . '/src/':'/tmp/wordpress/':" /tmp/wordpress-tests-lib/wp-tests-config.php
sed -i '' "s/youremptytestdbnamehere/wordpress_test_db/" /tmp/wordpress-tests-lib/wp-tests-config.php
sed -i '' "s/yourusernamehere/root/" /tmp/wordpress-tests-lib/wp-tests-config.php
sed -i '' "s/yourpasswordhere/root/" /tmp/wordpress-tests-lib/wp-tests-config.php
sed -i '' "s/localhost/localhost:8889/" /tmp/wordpress-tests-lib/wp-tests-config.php

# 5. Instalación de WooCommerce
echo "Descargando WooCommerce..."
curl -L https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip -o /tmp/woocommerce.zip
unzip -q /tmp/woocommerce.zip -d /tmp/wordpress/wp-content/plugins/

echo "✅ ¡Entorno de pruebas listo!"