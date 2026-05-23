<?php
use App\Models\Referrer;

$referrers = Referrer::where('code', 'like', 'REF-%')->get();
foreach ($referrers as $r) {
    $r->update(['code' => substr($r->code, 4)]);
}
echo "Done updating " . $referrers->count() . " records.\n";
