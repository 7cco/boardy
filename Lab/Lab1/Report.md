## параметры виртуальной машины в процессе создания (имя ВМ, память, диск)
![alt text](screenshots/01-vm-settings.png)

## Подключение  к PuTTY

![alt text](screenshots/PuTTY-con.png)

## командная строка Ubuntu после установки с приглашением фамилия@фамилия:~$
![alt text](screenshots/02-vm-console.png)

## вывод cat ~/report/01-system.txt

![alt text](screenshots/03-system-inf.png)

## вывод ip addr show
![alt text](screenshots/04-ip-addr.png)
## вывод sudo ss -tlnp
![alt text](screenshots/05-ports.png)
## вывод sudo systemctl status ssh
![alt text](screenshots/06-ssh-status.png)
## вывод sudo ss -tlnp | grep ssh
![alt text](screenshots/07-ssh-port.png)
## вывод grep '/bin/bash' /etc/passwd
![alt text](screenshots/08-user.png)
## процесс создания пользователя boardy
![alt text](screenshots/09-new-user.png)
![alt text](screenshots/image-7.png)
## вывод id boardy
![alt text](screenshots/10-user-chec.png)
## вывод ls -la /
![alt text](screenshots/11-root-tree.png)
## вывод ls -la ~
![alt text](screenshots/12-home-tree.png)
## вывод ls -ld / /etc /var /tmp /home
![alt text](screenshots/13-permissions.png)
## три состояния testfile.txt (до, после chmod 755, после chmod 600)
![alt text](screenshots/14-chmod.png)
## вывод dpkg -l | grep -E 'openssh|python|git|curl|vim|nano'
![alt text](screenshots/15-packages.png)
## вывод systemctl list-units --type=service --state=running
![alt text](screenshots/16-services.png)
## вывод ps aux --sort=-%mem | head -11
![alt text](screenshots/17-top-processes.png)
## вывод подсчёта процессов по пользователям
![alt text](screenshots/18-process-count.png)
## вывод топ-10 больших файлов в /var
![alt text](screenshots/19-big-files.png)
## вывод ls -lh ~/report/ со всеми файлами
![alt text](screenshots/20-report-files.png)