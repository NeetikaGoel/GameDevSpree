WORKING OF THIS PROJECT::

1. Open homepage
2. Click Add to Cart for the products user wanna buy
3. Click Cart
4. Lets user fill details
5. Submit form
6. Order will be saved in database


Purchase History → /php/product_history.php
Products Table → /php/products_table.php



HOW TO RUN THIS PROJECT NOW??


Open terminal in project folder and have to run:

npm init -y
npm install typescript --save-dev


Compile TypeScript to JS

Run:

### npx tsc


This will generate:

js/scripts.js

That is the file HTML will already be loading!


THEN START MYSQL SERVER FROM TERMINAL

### brew services start mysql
or maybe
### brew services restart mysql

LOGIN TO MYSQL

### mysql -u root -p

RUN FILE TO APPLY QUERIES

### SOURCE /Users/neetika.goel/Desktop/InternPrepWork/GameInventorySystem/sql/schema.sql;


RUN PHP SERVER

### php -S localhost:8004


OPEN IN BROWSER::

### http://localhost:8004/index.html




PROJECT ALL FILE STRUCTURE --TREEE


GameInventorySystem/
├── css
│   └── styles.css
├── index.html

├── order_success.html
├── payment.html
├── php
│   ├── db_connect.php
│   ├── get_product_history.php
│   ├── get_products.php
│   └── save_order.php
├── product_history.html
├── products_table.html
├── README.md
├── sql
│   └── schema.sql
├── ts
│   ├── payment.ts
│   ├── product_history.ts
│   ├── products_table.ts
│   └── script.ts
└── tsconfig.json


### CAN BE DONE FURTHERRRR


1. TO ADD PRODUCTS INFO IN DATABASE FROM FRONTEND ONLY
2. TO EDIT PRODUCTS INFO
3. TO DELETE PRODUCTS INFO
4. TO DELETE PURCHASE HISTORY INFO
5. MORE TO COMEEEE...
6. LOGIN AND AUTHENTICATION

