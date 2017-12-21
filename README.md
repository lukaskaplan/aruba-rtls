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
user@server:~$ __git clone https://github.com/lukaskaplan/aruba-rtls__
user@server:~$ __cd aruba-rtls__
user@server:~/aruba-rtls$ __mysql -u username -p rtls < database.sql__
user@server:~/aruba-rtls$ __mysql -u username -p__
mysql> GRANT ALL PRIVILEGES ON *.* TO 'rtls-user'@'%' IDENTIFIED BY 'password' WITH GRANT OPTION;
mysql> exit
user@server:~/aruba-rtls$ __mv ./config.php.exmaple ./config.php__
```
Then edit ./config.php, update RTLS key, RTLS port and  DB credentials.

## How to use it
### a) One time run
user@server:~/aruba-rtls$ __php ./rtls_server.php__

### b) Run it in background
user@server:~/aruba-rtls$ __nohup php ./rtls_server.php &>/dev/null &__

### How to stop it
```Bash
user@server:~$ __ps -aux | grep rtls__
user   32731  0.3  2.7 281456 26660 pts/0    S    13:18   0:00 php ./rtls_server.php      
user   32733  0.0  0.1  12784  1028 pts/0    S+   13:18   0:00 grep rtls      

user@server:~$ __kill 32731__
```
