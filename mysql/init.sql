CREATE DATABASE crop_production_db;

-- Create the customer table
CREATE TABLE customers (
    customer_id int auto_increment primary key,
    name varchar(255) not null,
    email varchar(255) not null,
    password varchar(255) not null,
    address varchar(255),
    phone_number varchar(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE farmers (
    farmer_id int auto_increment primary key,
    name varchar(255) not null,
    email varchar(255) not null,
    address varchar(255),
    farm_name varchar(255),
    location varchar(255),
    type_of_farmer varchar(255),
    description TEXT,
    phone_number varchar(10),
    media JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE products (
    product_id int auto_increment primary key,
    farmer_id int not null,
    name varchar(255) not null,
    category varchar(100) not null,
    description text,
    price_per_unit decimal(10, 2) not null,
    stock_quantity int default 0 not null,
    availability BOOLEAN default TRUE,
    status varchar(50) not null,
    delivery_option varchar(100) NOT NULL,
    foreign key (farmer_id) references farmers(farmer_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE crops (
    crop_id int auto_increment primary key,
    product_id int not null,
    planting_date date,
    expected_harvest date,
    growth_duration int,
    current_status varchar(50),
    foreign key (product_id) references products(farmer_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE chemical_usage (
    chemical_usage_id int auto_increment primary key,
    crop_id int not null,
    chemical_name varchar(255) not null,
    date_applied date,
    purpose varchar(255),
    amount_used decimal(10,2),
    foreign key (crop_id) references crops(crop_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    order_id int auto_increment primary key,
    customer_id int not null,
    product_id int not null,
    quantity_ordered int not null,
    total_price decimal(10,2) not null,
    ordered_date date not null,
    delivery_option varchar(50),
    ordered_status varchar(50),
    foreign key (customer_id) references customer(customer_id),
    foreign key (product_id) references products(product_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);