
#!/bin/bash
cd ../tars/

for file in *.php
do
if [ -f "$file" ]
then
  php ../src/vendor/phptars/tars2php/src/tars2php.php $file
fi
done