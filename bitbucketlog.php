<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
parked();
if (get_user_class() < UC_ADMINISTRATOR) {
    stderr("Sorry", "Access denied.");
}
$bucketpath = "$bitbucket";
if (get_user_class() >= UC_MODERATOR) {
    $delete = $_GET["delete"];
    if (is_valid_id($delete)) {
        $r = \NexusPHP\Components\Database::query("SELECT name,owner FROM bitbucket WHERE id=".\NexusPHP\Components\Database::real_escape_string($delete)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($r) == 1) {
            $a = mysqli_fetch_assoc($r);
            if (get_user_class() >= UC_MODERATOR || $a["owner"] == $CURUSER["id"]) {
                \NexusPHP\Components\Database::query("DELETE FROM bitbucket WHERE id=".\NexusPHP\Components\Database::real_escape_string($delete)) or sqlerr(__FILE__, __LINE__);
                if (!unlink("$bucketpath/$a[name]")) {
                    stderr("Warning", "Unable to unlink file: <b>$a[name]</b>. You should contact an administrator about this error.", false);
                }
            }
        }
    }
}
                                stdhead("BitBucket Log");
                                $res = \NexusPHP\Components\Database::query("SELECT count(*) FROM bitbucket") or die(\NexusPHP\Components\Database::error());	$row = mysqli_fetch_array($res);	$count = $row[0];
                                $perpage = 10;
                                list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?out=" . $_GET["out"] . "&");
                                print("<h1>BitBucket Log</h1>\n");
                                print("Total Images Stored: $count");
                                echo $pagertop;
                                $res = \NexusPHP\Components\Database::query("SELECT * FROM bitbucket ORDER BY added DESC $limit") or sqlerr(__FILE__, __LINE__);
                                if (mysqli_num_rows($res) == 0) {
                                    print("<b>BitBucket Log is empty</b>\n");
                                } else {
                                    print("<table align='center' border='0' cellspacing='0' cellpadding='5'>\n");
                                    while ($arr = mysqli_fetch_assoc($res)) {
                                        $date = substr($arr['added'], 0, strpos($arr['added'], " "));
                                        $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
                                        $name = $arr["name"];
                                        list($width, $height, $type, $attr) = getimagesize("" . get_protocol_prefix() . "$BASEURL/$bitbucket/$name");
                                        $url = str_replace(" ", "%20", htmlspecialchars("$bitbucket/$name"));
                                        print("<tr>");
                                        print("<td><center><a href=$url><img src=\"".$url."\" border=0 onLoad='SetSize(this, 400)'></a></center>");
                                        print("Uploaded by:  " . get_username($arr[owner]). "<br />");
                                        print("(#$arr[id]) Filename: $name ($width&nbsp;x&nbsp;$height)");
                                        if (get_user_class() >= UC_MODERATOR) {
                                            print(" <b><a href=?delete=$arr[id]>[Delete]</a></b><br />");
                                        }
                                        print("Added: $date $time");
                                        print("</tr>");
                                    }
                                    print("</table>");
                                }
                                        echo
                                        $pagerbottom;
                                        stdfoot();
