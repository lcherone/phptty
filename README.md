# phptty
A terminal in your browser using websocket php and  [workerman](https://github.com/walkor/Workerman), similar to [gotty](https://github.com/yudai/gotty).

Forked Changes:
 - Switched term.js to xterm.js
 - Tidy some codez
 - Changed it so it hooks directly into bash instead of a single process.

# Screenshot
![Screenshot](https://github.com/walkor/phptty/blob/master/Web/imgs/example.gif?raw=true)

# install
1. ```git clone https://github.com/lcherone/phptty```
2. ```cd phptty```
3. ```composer install```

# Start and stop
**start**  
```php start.php start -d```   

Visit ```http://ip:7779``` in your browser.

**stop**  
```php start.php stop```

# Related links
[https://github.com/yudai/gotty](https://github.com/yudai/gotty)  
[https://github.com/chjj/term.js](https://github.com/chjj/term.js)    
[https://github.com/walkor/Workerman](https://github.com/walkor/Workerman)    
