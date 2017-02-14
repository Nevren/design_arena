# v0.0.0
1. Install a stack (WAMP, MAMP, or LAMP)
For Mac: https://bitnami.com/stack/mamp (there are others as well)

2. If you'd like a GUI for mysql I'd reccomend either using phpMyAdmin or mysql work bench
https://www.mysql.com/products/workbench/ 
(phpMyAdmin is installed with bitnami)

3. All you need do in here is set up a schema called "design_arena", then import the included sql. 

4. From here just pull the project into the www directory or htdocs (whatever is being used by the stack) and access it via browser (ie 127.0.0.1/design_arena, 127.0.0.1:8080/design_arena, etc...)