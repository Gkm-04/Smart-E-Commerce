create database Ecommerce;
use Ecommerce;
create table Categories (
    cat_id int primary key auto_increment,
    cat_name varchar(100) not null unique,
	created_at timestamp default current_timestamp
);

create table Products (
    pro_id int primary key auto_increment,
    pro_name varchar(150) not null,
    description text,
    price decimal(10,2) not null check (price > 0),
	discount decimal(5,2) default 0 check (discount >= 0),
    stock int default 0 check (stock >= 0),
    image VARCHAR(255),
    created_at timestamp default current_timestamp,
    updated_at TIMESTAMP default current_timestamp on update current_timestamp,
    cat_id int,
    foreign key (cat_id) references Categories(cat_id)on delete set null on update cascade
);

create index idx_products_cat on Products(cat_id);
create index idx_products_price on Products(price);

create table Customers (
    cust_id int primary key auto_increment,
    first_name varchar(100) not null,
    last_name varchar(100) not null,
    email varchar(150) unique,
    password varchar(255),
    role enum('admin','customer') default 'customer',
    city varchar(100) default 'Gaza',
    created_at timestamp default current_timestamp
);

create index idx_customers_email on Customers(email);

create table Orders (
    order_id int primary key auto_increment,
    order_date date not null,
    status enum('pending','shipped','delivered','cancelled') default 'pending',
    total_amount decimal(10,2) default 0,
    cust_id int,
    created_at timestamp default current_timestamp,
    foreign key (cust_id) references Customers(cust_id)on delete set null on update cascade
);

create index idx_orders_customer on Orders(cust_id);

create table Order_Items (
    order_id int,
    pro_id int,
    quantity int not null check (quantity > 0),
    unit_price decimal(10,2) not null,
    primary key (order_id, pro_id),
    foreign key (order_id) references Orders(order_id)on delete cascade on update cascade,
    foreign key (pro_id) references Products(pro_id)on delete cascade on update cascade
);

insert into Categories(cat_name) values
('Electronics'),
('Clothing'),
('Books'),
('Sports'),
('Accessories');

insert into Products (pro_name, description, price, stock,image, cat_id) values
('Laptop Hp Core i5','Core i5 laptop',800,20,'LaptopA.jpg',1),
('Laptop Hp Core i7','Core i7 laptop',1100,15,'LaptopB.jpg',1),
('Laptop Asus ZenBook','Business laptop',1500,10,'LaptopC.jpg',1),
('Laptop Asus Tuf A16','Gaming laptop',900,15,'LaptopD.jpg',1),
('samsung s26 ultra','Smartphone',700,25,'SmartphoneA.jpg',1),
('iphone 17 pro max','Smartphone',1000,20,'SmartphoneB.jpg',1),
('xiaomi 15 ultra','Smartphone',500,30,'SmartphoneC.jpg',1),
('huawei pura 80 ultra','Smartphone',300,35,'SmartphoneD.jpg',1),
('10 inch tablet','Tablet A',350,20,'TabletA.jpg',1),
('12 inch tablet','Tablet B',450,15,'TabletB.jpg',1),
('Kids tablet','Tablet C',250,20,'TabletC.jpg',1),
('Pro tablet','Tablet D',600,10,'TabletD.jpg',1),
('Desktop PC 1','Office desktop',750,15,'DesktopA.jpg',1),
('Desktop PC 2','Gaming desktop',1600,8,'DesktopB.jpg',1),
('Desktop PC 3','Home desktop',650,12,'DesktopC.jpg',1),
('Computer Box','PC Case box',120,25,'PCBox.jpg',1),
('Real Madrid Kit','Official club kit',120,15,'KitA.jpg',2),
('Barcelona Kit','Official club kit',120,15,'KitB.jpg',2),
('Manchester City Kit','Official club kit',115,15,'KitC.jpg',2),
('Bayern Munich Kit','Official club kit',110,15,'KitD.jpg',2),
('Liverpool Kit','Official club kit',110,15,'KitE.jpg',2),
('PSG Kit','Official club kit',115,15,'KitF.jpg',2),
('Premium cotton shirt','T-Shirt Type 1',35,50,'Shirt1.jpg',2),
('Slim fit shirt','T-Shirt Type 2',30,50,'Shirt2.jpg',2),
('Hoodie','T-Shirt Type 3',32,50,'Shirt3.jpg',2),
('Sports shirt','T-Shirt Type 4',28,50,'Shirt4.jpg',2),
('Printed shirt','T-Shirt Type 5',40,50,'Shirt5.jpg',2),
('Basic casual shirt','T-Shirt Type 6',25,50,'Shirt6.jpg',2),
('Casual pants','Pants Type 1',50,40,'Pants1.jpg',2),
('Slim pants','Pants Type 2',55,40,'Pants2.jpg',2),
('Formal pants','Pants Type 3',60,30,'Pants3.jpg',2),
('Sports pants','Pants Type 4',45,40,'Pants4.jpg',2),
('Jeans pants','Pants Type 5',65,35,'Pants5.jpg',2),
('Full tracksuit','Tracksuit 1',85,20,'Tracksuit1.jpg',2),
('Training tracksuit','Tracksuit 2',80,20,'Tracksuit2.jpg',2),
('Winter tracksuit','Tracksuit 3',95,15,'Tracksuit3.jpg',2),
('Winter jacket','Jacket 1',120,20,'Jacket1.jpg',2),
('Light jacket','Jacket 2',90,25,'Jacket2.jpg',2),
('Sports jacket','Jacket 3',85,25,'Jacket3.jpg',2),
('Leather jacket','Jacket 4',150,10,'Jacket4.jpg',2),
('Casual shoes','Shoes Type 1',95,30,'Shoes1.jpg',2),
('Formal shoes','Shoes Type 2',110,25,'Shoes2.jpg',2),
('Java Programming','Learn Java',85,30,'java-programming.jpg',3),
('JavaScript Guide','Master JS',75,30,'javascript-guide.jpg',3),
('Data Structures','Data structures explained',90,25,'data-structures.jpg',3),
('Calculus','Differential calculus',70,20,'calculus.jpg',3),
('Chemistry Basics','Introduction to chemistry',65,20,'chemistry-basics.jpg',3),
('Physics Fundamentals','Learn physics',80,20,'physics-fundamentals.jpg',3),
('Be Yourself','Motivational book',50,30,'be-yourself.jpg',3),
('Less Talk','Self development book',45,30,'less-talk.jpg',3),
('SQL Book','Book for SQL learning',85,20,'sql-book.jpg',3),
('Python Book','Book for Python programming',65,20,'python-book.jpg',3),
('Harry Potter Series','Fantasy series',120,15,'harry-potter-series.jpg',3),
('Crime and Punishment','Classic novel',60,20,'crime-and-punishment.jpg',3),
('Atomic Habits','Self improvement',55,30,'atomic-habits.jpg',3),
('Time Investment','Productivity book',50,25,'time-investment.jpg',3),
('Seven Habits','Personal development',65,25,'seven-habits.jpg',3),
('Volleyball','Professional volleyball ball',25,50,'volleyball.jpg',4),
('Tennis Ball','High quality tennis ball',15,60,'Tennis_Ball.jpg',4),
('Sports Shoes Type A','Lightweight running shoes',90,30,'Sports_ShoesA.jpg',4),
('Sports Shoes Type B','Professional training shoes',110,25,'Sports_ShoesB.jpg',4),
('Tennis Racket New','Updated tennis racket model',70,15,'Tennis_Racket.jpg',4),
('Basketball New','Official size basketball',30,25,'Basketball.jpg',4),
('Football New','Standard soccer ball',30,25,'Football.jpg',4),
('Summer Sports Wear 1','Breathable summer set',40,40,'Sports_WearA.jpg',4),
('Summer Sports Wear 2','Lightweight training wear',45,35,'Sports_WearB.jpg',4),
('Winter Sports Wear 1','Warm winter tracksuit',75,20,'Sports_WearC.jpg',4),
('Winter Sports Wear 2','Thermal sports suit',85,18,'Sports_WearD.jpg',4),
('Long Sports Socks','Comfort long socks',10,80,'Sports_SocksA.jpg',4),
('Short Sports Socks','Comfort short socks',8,90,'Sports_SocksB.jpg',4),
('Travel backpack','Backpack 1',60,40,'Backpack1.jpg',5),
('Laptop backpack','Backpack 2',70,35,'Backpack2.jpg',5),
('School backpack','Backpack 3',45,50,'Backpack3.jpg',5),
('Sports backpack','Backpack 4',55,40,'Backpack4.jpg',5),
('Premium backpack','Backpack 5',90,20,'Backpack5.jpg',5),
('Wireless headphones','Headphones 1',150,25,'Headphones1.jpg',5),
('Gaming headset','Headphones 2',120,20,'Headphones2.jpg',5),
('Bluetooth earbuds','Headphones 3',80,30,'Headphones3.jpg',5),
('Fast charger','Charger 1',30,50,'Charger1.jpg',5),
('Wireless charger','Charger 2',45,40,'Charger2.jpg',5),
('Car charger','Charger 3',25,50,'Charger3.jpg',5),
('Multi USB charger','Charger 4',35,40,'Charger4.jpg',5),
('Smart watch','Watch 1',200,15,'Watch1.jpg',5),
('Classic watch','Watch 2',150,20,'Watch2.jpg',5),
('Polarized sunglasses','Sunglasses 1',70,30,'Sunglasses1.jpg',5),
('Sport sunglasses','Sunglasses 2',60,30,'Sunglasses2.jpg',5),
('Leather wallet','Wallet 2',50,40,'Wallet1.jpg',5),
('Slim wallet','Wallet 2',40,45,'Wallet2.jpg',5);

insert into Customers (first_name,last_name,email)values
('Ghassan','Khalil','ghassan@gmail.com'),
('Sara','Khaled','sara@gmail.com'),
('Omar','Yousef','omar@gmail.com'),
('Lina','Hassan','lina@gmail.com'),
('Ahmad','Ali','ahmad@gmail.com');

insert into Orders (order_date,cust_id)values
('2025-12-01',1),
('2026-01-01',2),
('2026-01-05',1),
('2026-01-10',3),
('2026-02-01',4),
('2026-02-01',2),
('2026-02-01',5),
('2026-02-01',3);

insert into Order_Items (order_id, pro_id,quantity,unit_price)values
(1,1,2,750),
(2,2,1,45),
(3,4,3,85),
(4,5,1,1200),
(6,6,5,65),
(7,8,5,30),
(8,10,2,200);

update Products
set price = price * 1.10
where cat_id = 4;

update Products
set discount = price * 0.20
where price > 150 and cat_id = 1;

select concat(first_name,' ',last_name) as Full_Name
from Customers;

select *
from Products
where pro_name like 'S%';

select *
from Products
where pro_name like 'A%'or price > 100;

select concat(upper(first_name),' ',upper(last_name)) as Full_Name
from Customers;

select *
from  Products
order by price desc;

select count(*) as Total_Orders
from Orders;

select avg(price) as Avg_Price
from Products;

select max(price) as Max_Price,
	min(Price) as Min_Price
from Products;

select cat_id ,count(*) as Total_Products
from Products
group by cat_id;

select pro_id ,sum(quantity) as Total_Quantity
from Order_Items
group by pro_id;

select c.cat_name,
       SUM(p.price) as Total_Category_Price
from Categories c
join Products p on c.cat_id = p.cat_id
group by c.cat_name;

select pro_id, SUM(quantity) as Total_Qty
from Order_Items
group by pro_id
having SUM(quantity) > 10;

select c.first_name, o.order_id
from Customers c
inner join Orders o
on c.cust_id = o.cust_id;

select c.first_name, o.order_id
from Customers c
left join Orders o
on c.cust_id = o.cust_id;

select c.cat_name, p.pro_name
from Categories c
right join Products p
on c.cat_id = p.cat_id;

select distinct cu.first_name, ca.cat_name
from Customers cu
join Orders o on cu.cust_id = o.cust_id
join Order_Items oi on o.order_id = oi.order_id
join Products p on oi.pro_id = p.pro_id
join Categories ca on p.cat_id = ca.cat_id;

select pro_name as name, 'Product' as source
from Products
union
select cat_name, 'Category'
from Categories;
