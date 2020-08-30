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
