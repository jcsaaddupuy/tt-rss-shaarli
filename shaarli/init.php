<?php
require_once "config.php";

class Shaarli extends Plugin {
  private $link;
  private $host;

  function about() {
    return array(0.0,
		 "Shaare your links ! (Sebsauvage Shaarli : http://sebsauvage.net/wiki/doku.php?id=php:shaarli )",
		 "jc.saaddupuy");
  }

  function init($host) {
    $this->link = $host->get_link();
    $this->host = $host;

    $host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
    $host->add_hook($host::HOOK_PREFS_TAB, $this);
  }

  function save() {
    $shaarli_url = db_escape_string($this->link,$_POST["shaarli_url"]);
    $this->host->set($this, "shaarli", $shaarli_url);
    echo "Value set to $shaarli_url";
  }

  function get_js() {
    return file_get_contents(dirname(__FILE__) . "/shaarli.js");
  }

  function hook_prefs_tab($args) {
    if ($args != "prefPrefs") return;

    print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("Shaarli")."\">";

    print "<br/>";

    $value = $this->host->get($this, "shaarli");
    print "<form dojoType=\"dijit.form.Form\">";

    print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
           evt.preventDefault();
           if (this.validate()) {
               console.log(dojo.objectToQuery(this.getValues()));
               new Ajax.Request('backend.php', {
                                    parameters: dojo.objectToQuery(this.getValues()),
                                    onComplete: function(transport) {
                                         notify_info(transport.responseText);
                                    }
                                });
           }
           </script>";

    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"shaarli\">";
    print "<table width=\"100%\" class=\"prefPrefsList\">";
        print "<tr><td width=\"40%\">".__("Shaarli url")."</td>";
	print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"shaarli_url\" regExp='^(http|https)://.*' value=\"$value\"></td></tr>";
    print "</table>";
    print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

    print "</form>";

    print "</div>"; #pane

  }

  function hook_article_button($line) {
    return "<img src=\"plugins/shaarli/shaarli.png\"
             style=\"cursor : pointer\" style=\"cursor : pointer\"
             onclick=\"shaarli(".$line["id"].")\"
             class='tagsPic' title='".__('Bookmark on Shaarli')."'>";
  }

  function getShaarli() {
    $id = db_escape_string($this->link,$_REQUEST['id']);

    $result = db_query($this->link, "SELECT title, link
		      FROM ttrss_entries, ttrss_user_entries
		      WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

    if (db_num_rows($result) != 0) {
      $title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
			       100, '...');
      $article_link = db_fetch_result($result, 0, 'link');
    }

    $shaarli_url = $this->host->get($this, "shaarli");

    print json_encode(array("title" => $title, "link" => $article_link,
			    "id" => $id, "shaarli_url" => $shaarli_url));
  }
}
?>
