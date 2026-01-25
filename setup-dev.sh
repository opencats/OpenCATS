#!/bin/bash
# OpenCATS Development Setup Script
# For adding REST API + Tearsheets features

set -e

echo "======================================"
echo "OpenCATS Development Environment Setup"
echo "======================================"

# Step 1: Clone the repository
echo ""
echo "[1/5] Cloning OpenCATS repository..."
if [ ! -d "OpenCATS" ]; then
    git clone https://github.com/opencats/OpenCATS.git
    cd OpenCATS
else
    cd OpenCATS
    git pull origin master
fi

# Step 2: Create feature branch
echo ""
echo "[2/5] Creating feature branch..."
git checkout -b feature/rest-api-tearsheets 2>/dev/null || git checkout feature/rest-api-tearsheets

# Step 3: Create directory structure for new features
echo ""
echo "[3/5] Creating new module directories..."

# API Module
mkdir -p modules/api
mkdir -p modules/tearsheets/templates

# Step 4: Create Docker Compose for development
echo ""
echo "[4/5] Creating Docker development environment..."

cat > docker-compose.dev.yml << 'EOF'
version: '3.8'

services:
  opencats:
    build:
      context: ./docker
      dockerfile: Dockerfile
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - DATABASE_HOST=db
      - DATABASE_USER=opencats
      - DATABASE_PASS=opencats
      - DATABASE_NAME=opencats

  db:
    image: mariadb:10.6
    ports:
      - "3306:3306"
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=opencats
      - MYSQL_USER=opencats
      - MYSQL_PASSWORD=opencats
    volumes:
      - db_data:/var/lib/mysql
      - ./db:/docker-entrypoint-initdb.d

  # Optional: PHPMyAdmin for database management
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8081:80"
    environment:
      - PMA_HOST=db
      - PMA_USER=opencats
      - PMA_PASSWORD=opencats
    depends_on:
      - db

volumes:
  db_data:
EOF

# Step 5: Show next steps
echo ""
echo "[5/5] Setup complete!"
echo ""
echo "======================================"
echo "NEXT STEPS:"
echo "======================================"
echo ""
echo "1. Start the development environment:"
echo "   docker-compose -f docker-compose.dev.yml up -d"
echo ""
echo "2. Wait for containers to initialize, then visit:"
echo "   http://localhost:8080"
echo ""
echo "3. Run the database migration:"
echo "   docker-compose -f docker-compose.dev.yml exec db mysql -u opencats -popencats opencats < db/migrations/001_add_api_and_tearsheets.sql"
echo ""
echo "4. Start coding the API module in:"
echo "   modules/api/ApiUI.php"
echo ""
echo "5. Test the API:"
echo "   curl http://localhost:8080/index.php?m=api&a=joborders"
echo ""
echo "======================================"
echo "Happy coding! 🚀"
echo "======================================"
