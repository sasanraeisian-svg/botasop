<?php
echo "Step 1: start\n";

include "config/configs.php";
echo "Step 2: configs.php loaded\n";

include "config/updates.php";
echo "Step 3: updates.php loaded\n";
echo "text=" . ($text ?? 'UNSET') . "\n";
echo "type=" . ($type ?? 'UNSET') . "\n";
echo "from_id=" . ($from_id ?? 'UNSET') . "\n";

include "config/keyboards.php";
echo "Step 4: keyboards.php loaded\n";

include "function/functions.php";
echo "Step 5: functions.php loaded\n";

include "function/jdf.php";
echo "Step 6: jdf.php loaded\n";

echo "ALL INCLUDES OK\n";
