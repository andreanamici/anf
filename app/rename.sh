#cambia l'estenzione a tutti i file di una directory
for file in *; do
    ext=${file##*.}
    fname=`basename $file .conf.php`
    mv $file $fname.php
    rm $file
done;
