#!/bin/bash
set -e

MYSQL_DATA_DIR="/home/runner/mysql_data"
MYSQL_SOCKET="/tmp/mysql.sock"
MYSQL_LOG="/tmp/mysql_error.log"

echo "=== Démarrage IRS ==="

# Initialiser MariaDB si nécessaire
if [ ! -d "$MYSQL_DATA_DIR/mysql" ]; then
    echo "Initialisation de la base de données MariaDB..."
    mysql_install_db \
        --user=$(whoami) \
        --datadir="$MYSQL_DATA_DIR" \
        --auth-root-authentication-method=normal \
        --skip-test-db \
        > /dev/null 2>&1
    echo "MariaDB initialisé."
fi

# Démarrer MariaDB si pas déjà en cours
if ! mysqladmin --socket="$MYSQL_SOCKET" ping > /dev/null 2>&1; then
    echo "Démarrage de MariaDB..."
    mysqld \
        --datadir="$MYSQL_DATA_DIR" \
        --socket="$MYSQL_SOCKET" \
        --pid-file="/tmp/mysql.pid" \
        --port=3306 \
        --bind-address=127.0.0.1 \
        --log-error="$MYSQL_LOG" \
        --skip-networking=OFF \
        &

    # Attendre que MariaDB soit prêt
    echo "Attente de MariaDB..."
    for i in $(seq 1 30); do
        if mysqladmin --socket="$MYSQL_SOCKET" ping > /dev/null 2>&1; then
            echo "MariaDB prêt !"
            break
        fi
        sleep 1
    done
fi

# Créer la base de données si elle n'existe pas
DB_EXISTS=$(mysql --socket="$MYSQL_SOCKET" -u root -e "SHOW DATABASES LIKE 'irs_db';" 2>/dev/null | grep -c "irs_db" || true)

if [ "$DB_EXISTS" -eq 0 ]; then
    echo "Création et import de la base de données..."
    mysql --socket="$MYSQL_SOCKET" -u root < database/irs_database.sql
    echo "Base de données importée avec succès !"
else
    echo "Base de données irs_db déjà présente."
fi

# Mise à jour des identifiants admin et du contact
echo "Mise à jour des identifiants administrateur..."
mysql --socket="$MYSQL_SOCKET" -u root irs_db <<'SQL'
UPDATE admins SET email='aldofoch@gmail.com', password='1214161820@Ben', username='aldofoch' WHERE id=1;
INSERT INTO settings (setting_key, setting_value)
  VALUES ('site_email', 'internationalregistrationserve@gmail.com')
  ON DUPLICATE KEY UPDATE setting_value='internationalregistrationserve@gmail.com';
SQL

echo ""
echo "=== Serveur PHP démarré sur le port 5000 ==="
echo "Admin : aldofoch@gmail.com / 1214161820@Ben"
echo "User  : jean.dupont@email.com / User@1234"
echo ""

# Démarrer le serveur PHP built-in sur le port 5000
php -S 0.0.0.0:5000 -t . router.php
