#!/usr/local/bin/php-cgi -f
<?php
require_once("config.inc");
require_once("interfaces.inc");
require_once("plugins.inc.d/dpinger.inc");
require_once("util.inc");

$host = gethostname();
$source = "pfconfig";

$iflist = get_configured_interface_with_descr();
foreach ($iflist as $ifname => $friendly) {
    $ifsinfo = get_interfaces_info();
    $ifinfo = $ifsinfo[$ifname];
    $ifstatus = $ifinfo['status'];
    $realif = get_real_interface($ifname);
    $ip4addr = get_interface_ip($ifname);
    $ip4subnet = interfaces_primary_address($realif, $ifconfig_details)[1];
    $ip6addr = get_interface_ipv6($ifname);
    $ip6subnet = interfaces_primary_address6($realif, $ifconfig_details)[1];
    $mac = get_interface_mac($realif);

    if (!isset($ifinfo)) {
        $ifinfo = "Unavailable";
    }
    if (strtolower($ifstatus) == "up") {
        $ifstatus = 1;
    }
    if (strtolower($ifstatus) == "active") {
        $ifstatus = 1;
    }
    if (strtolower($ifstatus) == "no carrier") {
        $ifstatus = 0;
    }
    if (strtolower($ifstatus) == "down") {
        $ifstatus = 0;
    }
    if (!isset($ifstatus)) {
        $ifstatus = 2;
    }
    if (!isset($ip4addr)) {
        $ip4addr = "Unassigned";
    }
    if (!isset($ip4subnet)) {
        $ip4subnet = "Unassigned";
    }
    if (!isset($ip6addr)) {
        $ip6addr = "Unassigned";
    }
    if (!isset($ip6subnet)) {
        $ip6subnet = "Unassigned";
    }
    if (!isset($realif)) {
        $realif = "Unassigned";
    }
    if (!isset($mac)) {
        $mac = "Unavailable";
    }

    printf(
        "interface,host=%s,name=%s,ip4_address=%s,ip4_subnet=%s,ip6_address=%s,ip6_subnet=%s,mac_address=%s,friendlyname=%s,source=%s status=%s\n",
        $host,
        $realif,
        $ip4addr,
        $ip4subnet,
        $ip6addr,
        $ip6subnet,
        $mac,
        $friendly,
        $source,
        $ifstatus,
    );
}

$gw_array = (new \OPNsense\Routing\Gateways(legacy_interfaces_details()))->gatewaysIndexedByName();
$gw_statuses = return_gateways_status();

$debug = false;

if ($debug) {
    print_r($gw_array);
    print_r($gw_statuses);
}

foreach ($gw_array as $gw => $gateway) {

    $name = $gateway["name"];

    $delay = $gw_statuses[$gw]["delay"];
    $stddev = $gw_statuses[$gw]["stddev"];
    $status = $gw_statuses[$gw]["status"];
    $loss = $gw_statuses[$gw]["loss"];

    $interface = $gateway["interface"];
    $gwdescr = $gateway["descr"];
    $monitor = $gateway["monitor"];
    $source = $gateway["gateway"];
    
    if (!isset($monitor)) {
        $monitor = "Unavailable";
    }
    if (!isset($source)) {
        $source = "Unavailable";
    }
    if (!isset($delay)) {
        $delay = "0";
    }
    if (!isset($stddev)) {
        $stddev = "0";
    }
    if (!isset($loss)) {
        $loss = "0";
    }
    if (strtolower($status) == "none") {
        $status = 1;
    }
    if (strtolower($status) == "force_down") {
        $status = 0;
    }
    if (strtolower($status) == "down") {
        $status = 0;
    }
    if (!isset($status)) {
        $status = "Unavailable";
    }
    if (!isset($interface)) {
        $interface = "Unassigned";
    }
    if (!isset($gwdescr)) {
        $gwdescr = "Unassigned";
    }
    if (isset($gateway['monitor_disable'])) {
        $monitor = "Unmonitored";
    }

    printf(
        "gateways,host=%s,interface=%s,gateway_name=%s monitor=\"%s\",source=\"%s\",gwdescr=\"%s\",delay=%s,stddev=%s,loss=%s,status=\"%s\"\n",
        $host,
        $interface,
        $name,
        $monitor,
        $source,
        $gwdescr,
        floatval($delay),
        floatval($stddev),
        floatval($loss),
        $status
    );
};
?>
