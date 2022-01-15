reset

echo "UltimateWoltlabMaster von Justman10000"
echo "Dies ist unter der GNU General Public License v3.0 lizenziert"

echo "Was soll getan werden?"
echo "Installieren [1]"
echo "Deinstallieren [2]"
echo "Zurück [3]"
read WhatDo

clear

FileName=WoltlabSuite.zip

case $WhatDo in
    "1")
        wget https://assets.woltlab.com/release/woltlab-suite-5.4.10.zip
        sleep 1
        mv woltlab-suite-5.4.10.zip $FileName
        sleep 1
        unzip $FileName
        sleep 1
        rm -r *.txt
        sleep 1

        clear

        echo "In welchem Pfad soll WoltLab installiert werden?"
        read WhereInstall

        clear

        if [ "$WhereInstall" == "." ]; then
            mv upload/* .
            sleep 1
            rm -r upload
            sleep 1
            rm -r *.sh
        fi

        if ! [ "$WhereInstall" == "." ]; then
            if ! [ -d $WhereInstall ]; then
                mkdir $WhereInstall
            fi

            mv upload/* $WhereInstall
            sleep 1
            rm -r upload
            sleep 1
            rm -r *.sh
        fi

        echo "Soll die test.php Datei beim Aufruf der URL aufgerufen werden? Dadurch kann Woltlab installiert werden,"
        echo "ohne die install.php oder test.php direkt aufrufen zu müssen"
        sleep 2
        echo "-------------------------------------------------------------------------------------------------------"
        echo "Warnung, sollte die Installation nicht direkt durchgeführt werden, wird die Antwort Nein empfohlen"
        echo "Siehe: https://www.woltlab.com/community/thread/293784-test-php-index-php"
        sleep 2
        echo "-------------------------------------------------------------------------------------------------------"
        echo "Ja   [1]"
        echo "Nein [2]"
        read Rename

        clear

        case $Rename in
            "1")
                mv $WhereInstall/test.php $WhereInstall/index.php
            ;;

            "2")
            ;;

            *)
                echo "Ungültige Antwort"
                sleep 2

                bash UltimateWoltlabMaster.sh
            ;;
        esac

        clear

        rm -r $FileName
        sleep 1
        chmod -R 777 $WhereInstall/*
        sleep 2

        bash UltimateWoltlabMaster.sh
    ;;

    "2")
        echo "In welchem Verzeichnis ist Woltlab Installiert?"
        read WhereInstalled

        clear

        rm -r $WhereInstalled/*
        sleep 1
        rm -r $WhereInstalled

        echo "Bedenke, dass dieses Script nicht die Datenbank lerren oder entfernen kann"
        echo "Tue dies daher manuell..."
        sleep 2

        bash UltimateWoltlabMaster.sh
    ;;

    "3")
        exit
    ;;

    *)
        echo "Ungültige Antwort"
        sleep 2

        bash UltimateWoltlabMaster.sh
    ;;
esac

reset