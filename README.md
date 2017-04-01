QUIQQER Log
========

Ergänzt QUIQQER um eine erweiterte Logverwaltung.

Paketname:

    quiqqer/log


Features
--------

- Erweiterte Logverwaltung
- JavaScript Fehler-Loging
- Log-Level-Einstellungen
- FirePHP, ChromePHP Loging (Browser Debuging)
- Kann Logs zu einem Cube Server senden
- Kann Logs zu einem New Relic senden
- Kann Logs zu einem Syslog UDP Server senden


Installation
------------

Der Paketname ist: quiqqer/log


Mitwirken
----------

- Issue Tracker: https://dev.quiqqer.com/quiqqer/package-log/issues
- Source Code: https://dev.quiqqer.com/quiqqer/package-log/tree/master


Support
-------

Falls Sie Fehler gefunden, Wünsche oder Verbesserungsvorschläge haben, 
können Sie uns gern per Mail an support@pcsg.de darüber informieren.  
Wir werden versuchen auf Ihre Wünsche einzugehen bzw. diese an die 
zuständigen Entwickler des Projektes weiterleiten.


License
-------


Entwickler
--------

Erweitert QUIQQER um ein neues globales Event
- onQuiqqerLogGetLogger [ Monolog\Logger $Logger ]

Wird gefeuert wenn ein Monolog Logger initialisiert wurde.
Mit diesem Event können neue Monolog Logger dem $Logger hinzugefügrt werden.

onQuiqqerLogGetLogger