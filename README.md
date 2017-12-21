# Simple Aruba RTLS collector
## Description
This simple scripts are used for collecting RTLS information from Aruba wireless controller. Received data are stored in database.

This project is still in proof of concept phase. It is not suitable for production use !!!

## Instalation

### Prerequisites:
 * php-cli
 * mariadb / mysql server

### How to install it:
```Bash
user@server:~$ git clone https://github.com/lukaskaplan/aruba-rtls
user@server:~$ cd aruba-rtls
user@server:~/aruba-rtls$ mysql -u username -p rtls < database.sql
user@server:~/aruba-rtls$ mysql -u username -p
mysql> GRANT ALL PRIVILEGES ON *.* TO 'rtls-user'@'%' IDENTIFIED BY 'password' WITH GRANT OPTION;
mysql> exit
user@server:~/aruba-rtls$ mv ./config.php.exmaple ./config.php
```
Then edit ./config.php, update RTLS key, RTLS port and  DB credentials.

## How to use it
### a) One time run
```Bash
user@server:~/aruba-rtls$ __php ./rtls_server.php__
```

### b) Run it in background
```Bash
user@server:~/aruba-rtls$ __nohup php ./rtls_server.php &>/dev/null &__
```

### How to stop it
```Bash
user@server:~$ __ps -aux | grep rtls__
user   32731  0.3  2.7 281456 26660 pts/0    S    13:18   0:00 php ./rtls_server.php      
user   32733  0.0  0.1  12784  1028 pts/0    S+   13:18   0:00 grep rtls      

user@server:~$ __kill 32731__
```
