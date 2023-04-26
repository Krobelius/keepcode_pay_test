<?php


$STRIPE_KEY = "1234567890";
$ALIPAY_KEY = "11223344";

$data = json_decode(file_get_contents("payments.json"), true);

$database = new mysqli("localhost", "root", "root", "test");//use your database credentials instead defaults


foreach ($data['list'] as $item) {
	if ($item["system"] == "stripe") {
		$sign = $item["sign"];
		$blocks = $item["blocks"];
		if (md5(json_encode($blocks) . $STRIPE_KEY) !== $sign) {
			die();
		}
		$summa = 0;
		$last = array_shift($blocks);
		foreach ($blocks as $block) {
			$summa += $block["sum"];
		}
		if ($summa < $item["minimal"]) {
			die();
		}
		$date = $last["date"];
		$searchPayment = $database->query("SELECT * FROM payments_test WHERE sign = $sign");
		if ($searchPayment->num_rows > 0) {
			die();
		}
		$database->query("INSERT INTO payments_test(system,sum,date) VALUES ('stripe',$summa,$date)");
	} else if ($item["system"] == "alipay") {
		$sign = $item["paymentSign"];
		if ($sign !== md5($item['referenceId'] . $ALIPAY_KEY)) {
			die();
		}
		$summa = $item['paymentSum'];
		$date = $item['2023-04-04 21:00:00'];
		$searchPayment = $database->query("SELECT * FROM payments_test WHERE sign = $sign");
		if ($searchPayment->num_rows > 0) {
			die();
		}
		$database->query("INSERT INTO payments_test(system,sum,date) VALUES ('alipay',$summa,$date)");
	}
}