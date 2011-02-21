#!/bin/bash
TEMP_FILE_NAME=/tmp/dump_db.sql

MYSQL_LOCAL_USER=cats
MYSQL_LOCAL_PASSWORD=password
MYSQL_LOCAL_ARGS="-u${MYSQL_LOCAL_USER} -p${MYSQL_LOCAL_PASSWORD}"

MYSQL_PROD_HOST=10.0.0.66
MYSQL_PROD_USER=sae
MYSQL_PROD_PASSWORD=sae99
MYSQL_PROD_ARGS="-u${MYSQL_PROD_USER} -p${MYSQL_PROD_PASSWORD} -h${MYSQL_PROD_HOST}"

HELP_ARGS="mysql_get_prod_db USERNAME PASSWORD DATABASE_NAME\nCopy production database DATABASE_NAME to local MySQL server. Must supply username/password for production server."

if [ $# -ne 3 ];
then
    echo -e $HELP_ARGS
    exit 1
fi

MYSQL_PROD_USER=$1
MYSQL_PROD_PASSWORD=$2

# Check if the database exists on the production server
DB_PROD_EXISTS=`/usr/bin/mysqlshow $MYSQL_PROD_ARGS | grep "$3"`
if [ "$DB_PROD_EXISTS" == "" ];
then
    echo "That database does not exist on the production server."
    exit 1
fi

# Check if the database exists locally
DB_LOCAL_EXISTS=`/usr/bin/mysqlshow $MYSQL_LOCAL_ARGS | grep "$3"`
if [ "$DB_LOCAL_EXISTS" == "" ];
then
    echo -n "Database does not exist locally, creating it... "
    /usr/bin/mysqladmin $MYSQL_LOCAL_ARGS create "$3" && echo "[OK]" || function errCreateDB {
        echo "Failed to create local database \"$3\". Aborting."
        exit 1
    }
fi

echo -n "Connecting to database $3 on MySQL production server ($MYSQL_PROD_HOST:3306) using username \"$MYSQL_PROD_USER\" with password (yes) and attempting to dump all tables from $3 to $TEMP_FILE_NAME... "
/usr/bin/mysqldump $MYSQL_PROD_ARGS $3 > "$TEMP_FILE_NAME" && echo "[OK]" || function f {
    echo "[FAILED]"
    echo
    echo "Aborting synchronization with production database."
    exit 1
}

if [ ! -f "$TEMP_FILE_NAME" ];
then
    echo "Failed to create file \"$TEMP_FILE_NAME\"! Aborting."
    exit 1
fi

FILE_SIZE=`ls -sh $TEMP_FILE_NAME | sed -e 's/ .*//'`
echo "Dumped $FILE_SIZE of data to $TEMP_FILE_NAME successfully."

echo -n "Copying data to local MySQL database \"$3\" using user \"$MYSQL_LOCAL_USER\" with password (yes)... "
/usr/bin/mysql $MYSQL_LOCAL_ARGS $3 < "$TEMP_FILE_NAME" && echo "[OK]" || function f2 {
    echo "[FAILED]"
    echo
    echo -n "Removing temporary file \"$TEMP_FILE_NAME\"... "
    rm "$TEMP_FILE_NAME" && echo "[OK]" || echo "[FAILED]"
    echo "Aborting synchronization with the production database."
    exit 1
}

echo -n "Removing temporary file \"$TEMP_FILE_NAME\"... "
rm "$TEMP_FILE_NAME" && echo "[OK]" || echo "[FAILED]"

echo "Successfully copied $FILE_SIZE of data to local database."
