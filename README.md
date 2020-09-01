# eos.uptrade Coding Challenge

Dieses Repository enthält <a href="https://github.com/Fonata" title="Christian Bläul">meinen</a> Lösungsvorschlag zu folgender Aufgabenstellung:

<i>"Um Dich technisch noch etwas besser einschätzen zu können, bitten wir Dich, das Spiel „Schiffe versenken“ in PHP zu implementieren.

Die offiziellen Regeln sind unter [www.hasbro.com/common/instruct/Battleship.PDF](https://www.hasbro.com/common/instruct/Battleship.PDF) zu finden.
Bitte implementiere die Spiellogik so, dass das Spiel über eine JSON REST API gespielt werden kann. Dabei soll der
Consumer der API gegen einen simulierten (zufällig, aber taktisch agierenden) Gegner spielen.

Der aktuelle Spielstatus soll über eine einfache Index-Seite ersichtlich sein. Gerne darf dies auch um eine
Spielstatistik erweitert werden. Die Beispielaufrufe des Consumers sollen in Form eines
[Postman-Projektes](https://www.getpostman.com/) mitgeliefert werden.

Das verwendete Framework soll dabei [Symfony](https://symfony.com/) sein.

Die Art der Umsetzung ist Dir völlig freigestellt. Deine Lösung sollte den Stand repräsentieren, den Du unter einer
professionellen Softwareentwicklung im PHP-Umfeld verstehst. Es wäre toll, wenn Du uns Deine Antwort mit einem
GitHub-Link und einer kleiner Readme sendest. Viel Spaß!"</i>

## Orte im Netz
Eine Onlinefassung läuft unter [battleship.blaeul.de](https://battleship.blaeul.de).

Die Quellen liegen in einem privaten Repository bei GitHub: https://github.com/Fonata/eos.uptrade-battleship

## Verzeichnisstruktur
- .github: Die GitHub-Actions, die die Tests ausführen
- .idea: Die PhpStorm-Einstellungen
- api: Die Symfony-Anwendung. Der Client sitzt in `templates\game_client\index.html.twig`.
- tests: Die Behat-Tests für die API

## Details zur technischen Umsetzung
Die API ist mithilfe des Symfony-Frameworks 5.1 in PHP 7.4 geschrieben. Für die automatische Dokumentation kommt
[API Platform](https://github.com/api-platform/api-platform) zum Einsatz.

Für die Datenspeicherung ist Doctrine für MySQL vorkonfiguriert.

## Installation
Klone dieses Repository innerhalb deiner lokalen Umgebung, zum Beispiel mit:
````bash
git clone https://github.com/Fonata/eos.uptrade-battleship /var/www/battleship.blaeul.de
cd /var/www/battleship.blaeul.de
````

### Lokale Ausführung ohne Docker
Dieser Abschnitt setzt voraus, dass Bash, PHP ≥ 7.4 und Composer installiert sind. Falls das nicht der Fall ist, dann
kannst du ein Dockerimage ausführen und darin arbeiten. Ein Beispiel ist [unten](#api-und-tests-innerhalb-von-docker-ausfhren) beschrieben.

Abhängigkeiten mit Composer installieren:
````bash
cd api
composer install
````

Die API benötigt eine Datenbank. Die in diesem Repo committete Konfiguration ist für eine lokal laufende
MySQL 5.7-Instanz vorbereitet. Falls die Datenbank woanders läuft oder nicht MySQL ist, dann kann es in der Datei
`api/.env` auf der Zeile `DATABASE_URL` geändert werden.

Um die Datenbank zu initialisieren kann die Datei `init-db.sh` ausgeführt werden.

#### API starten
Nur das Verzeichnis `api/public` muss über HTTP(S) verfügbar gemacht werden.

Während der Entwicklung ist die <a href="https://symfony.com/download">Symfony CLI</a> dafür ein guter Weg. Um sie mit
diesem Repo zu nutzen, bitte die folgenden Schritte ausführen:
````bash
cd api
symfony server:start --port=8003 --daemon
````

#### Automatische Tests
Damit die Tests erfolgreich sind, muss die API laufen. Die Tests sind so vorkonfiguriert, dass die API lokal (127.0.0.1)
und auf Port 8003 läuft. Falls das nicht so ist, bitte die Datei `behat.yml` anpassen.

Um die Tests auszuführen sind die folgenden Kommandos nötig:
````bash
cd tests/behat
composer install
vendor/bin/behat
````

### API und Tests innerhalb von Docker ausführen
Statt einer lokalen Installation von Composer, PHP, Behat und Symfony lassen sich API und Tests auch in einem
Docker-Container ausführen.

Vor dem Ausführen des folgenden Kommandos bitte prüfen, dass du im Stammverzeichnis des geklonten Repos bist - dort wo
diese `README.md`-Datei liegt.

````bash
docker run -it --rm --publish 8003:8003 --name eos.uptrade \
 -v "$PWD":/var/www/eos.uptrade-coding-challenge \
 -w /var/www/eos.uptrade-coding-challenge \
 chialab/php:7.4 bash -c '
  export COMPOSER_ALLOW_SUPERUSER=1
  alias "ll=ls -lsa"
  apt update
  apt install -y mariadb-server git unzip nano sudo
  cd /var/www/eos.uptrade-coding-challenge
  cd api
  composer validate
  composer install
  cd ../tests/behat
  composer validate
  composer install
  cd ../..
  ./init-db.sh
  php -S 0.0.0.0:8003 -t api/public &
  cd tests/behat
  php vendor/bin/behat
  bash
  '
````

Das obige Bash-Kommando
  * installiert alle Abhängigkeiten
  * erstellt die Datenbank
  * startet die API-Anwendung
  * führt alle Behat-Tests aus
  * hinterlässt dich in einer Bash innerhalb von Docker
