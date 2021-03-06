<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2018 Marcin Łojewski <dev@mlojewski.me>
 * @author    Marcin Łojewski <dev@mlojewski.me>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use OCP\IL10N;

script('user_sql', 'settings');
style('user_sql', 'settings');

function print_text_input(IL10N $l, $id, $label, $value = "", $type = "text")
{
    echo "<div><label for=\"$id\"><span>";
    echo p($l->t($label));
    echo "</span><input type=\"$type\" id=\"$id\" name=\"$id\" value=\"";
    echo p($value);
    echo "\">";
    echo "</label></div>";
}

function print_checkbox_input(IL10N $l, $id, $label, $value = "", $div = true)
{
    if ($div) {
        echo "<div>";
    }
    echo "<input type=\"checkbox\" id=\"$id\" name=\"$id\" value=\"1\"";
    if ($value === true) {
        echo " checked";
    }
    echo ">";
    echo "<label for=\"$id\">";
    echo p($l->t($label));
    echo "</label>";
    if ($div) {
        echo "</div>";
    }
}

function print_select_options(
    IL10N $l, $id, $label, $options = [], $select = false
) {
    echo "<div><label for=\"$id\"><span>";
    echo p($l->t($label));
    echo "</span><select id=\"$id\" name=\"$id\">";

    foreach ($options as $name => $value) {
        echo "<option ";
        if ($select === $name) {
            echo "selected=\"selected\"";
        }
        echo "value=\"";
        echo $name;
        echo "\">";
        echo p($value);
        echo "</option>";
    }

    echo "</select>";
    echo "</label></div>";
}

?>
<form id="user_sql" action="#" method="post">
    <div id="user_sql-msg" class="msg" style="display: none">
        <p id="user_sql-msg-body"></p>
    </div>
    <div class="section">
        <p>
            <?php p($l->t("This is the place for ")); ?>
            <b><?php p($l->t("User and Group SQL Backends")); ?></b>
            <?php p($l->t(" app settings. Please see the documentation for more information.")); ?>
            <a target="_blank" rel="noreferrer noopener" class="icon-info" title="Open documentation" href="https://github.com/nextcloud/user_sql"></a>
        </p>
    </div>
    <div class="main">
        <div class="section">
            <h2><?php p($l->t("Database connection")); ?></h2>
            <p class="settings-hint"><?php p($l->t("Define your database connection parameters.")); ?></p>
            <fieldset><?php
                $drivers = ["mysql" => "MySQL", "pgsql" => "PostgreSQL"];
                print_select_options($l, "db-driver", "SQL driver", $drivers, $_['db.driver']);
                print_text_input($l, "db-hostname", "Hostname", $_['db.hostname']);
                print_text_input($l, "db-database", "Database", $_['db.database']);
                print_text_input($l, "db-username", "Username", $_['db.username']);
                print_text_input($l, "db-password", "Password", $_['db.password'], "password"); ?>
                <div class="button-right">
                    <input type="submit" id="user_sql-db_connection_verify" value="<?php p($l->t("Verify settings")); ?>">
                </div>
            </fieldset>
        </div>
        <div class="section">
            <h2><?php p($l->t("Options")); ?></h2>
            <p class="settings-hint"><?php p($l->t("Here are all currently supported options.")); ?></p>
            <fieldset><?php
                print_checkbox_input($l, "opt-name_change", "Allow display name change", $_['opt.name_change']);
                print_checkbox_input($l, "opt-password_change", "Allow password change", $_['opt.password_change']); ?>
                <div class="button-right"><?php
                    print_checkbox_input($l, "opt-use_cache", "Use cache", $_['opt.use_cache'], false); ?>
                    <input type="submit" id="user_sql-clear_cache" value="<?php p($l->t("Clear cache")); ?>">
                </div>
                <?php
                $hashing = [];
                foreach (glob(__DIR__ . "/../lib/Crypto/*.php") as $filename) {
                    $class = 'OCA\\UserSQL\\Crypto\\' . basename(substr($filename, 0, -4));
                    try {
                        $passwordAlgorithm = new $class($l);
                        if ($passwordAlgorithm instanceof
                            \OCA\UserSQL\Crypto\IPasswordAlgorithm
                        ) {
                            $hashing[$class] = $passwordAlgorithm->getVisibleName();
                        }
                    } catch (Throwable $e) {
                    }
                }

                print_select_options($l, "opt-crypto_class", "Hashing algorithm", $hashing, $_['opt.crypto_class']);
                print_select_options($l, "opt-email_sync", "Email sync", ["" => "None", "initial" => "Synchronise only once", "force_nc"=>"Nextcloud always wins", "force_sql"=>"SQL always wins"], $_['opt.email_sync']);
                print_select_options($l, "opt-home_mode", "Home mode", ["" => "Default", "query" => "Query", "static" => "Static"], $_['opt.home_mode']);
                print_text_input($l, "opt-home_location", "Home Location", $_['opt.home_location']); ?>
            </fieldset>
        </div>
        <div class="section clear-left">
            <h2><?php p($l->t("User table")); ?></h2>
            <p class="settings-hint"><?php p($l->t("Table containing user accounts.")); ?></p>
            <fieldset><?php
                print_text_input($l, "db-table-user", "Table name", $_['db.table.user']) ;?>
                <h3><?php p($l->t("Columns")); ?></h3>
                <?php
                print_text_input($l, "db-table-user-column-uid", "Username", $_['db.table.user.column.uid']);
                print_text_input($l, "db-table-user-column-email", "Email", $_['db.table.user.column.email']);
                print_text_input($l, "db-table-user-column-home", "Home", $_['db.table.user.column.home']);
                print_text_input($l, "db-table-user-column-password", "Password", $_['db.table.user.column.password']);
                print_text_input($l, "db-table-user-column-name", "Display name", $_['db.table.user.column.name']);
                print_text_input($l, "db-table-user-column-active", "Active", $_['db.table.user.column.active']);
                print_text_input($l, "db-table-user-column-avatar", "Can change avatar", $_['db.table.user.column.avatar']);
                print_text_input($l, "db-table-user-column-salt", "Salt", $_['db.table.user.column.salt']); ?>
            </fieldset>
        </div>
        <div class="section">
            <h2><?php p($l->t("Group table")); ?></h2>
            <p class="settings-hint"><?php p($l->t("Group definitions table.")); ?></p>
            <fieldset><?php
                print_text_input($l, "db-table-group", "Table name", $_['db.table.group']); ?>
                <h3><?php p($l->t("Columns")); ?></h3>
                <?php
                print_text_input($l, "db-table-group-column-admin", "Is admin", $_['db.table.group.column.admin']);
                print_text_input($l, "db-table-group-column-name", "Display name", $_['db.table.group.column.name']);
                print_text_input($l, "db-table-group-column-gid", "Group name", $_['db.table.group.column.gid']); ?>
            </fieldset>
        </div>
        <div class="section">
            <h2><?php p($l->t("User group table")); ?></h2>
            <p class="settings-hint"><?php p($l->t("Associative table which maps users to groups.")); ?></p>
            <fieldset><?php
                print_text_input($l, "db-table-user_group", "Table name", $_['db.table.user_group']); ?>
                <h3><?php p($l->t("Columns")); ?></h3>
                <?php
                print_text_input($l, "db-table-user_group-column-uid", "Username", $_['db.table.user_group.column.uid']);
                print_text_input($l, "db-table-user_group-column-gid", "Group name", $_['db.table.user_group.column.gid']); ?>
            </fieldset>
        </div>
    </div>
    <div class="section">
        <input type="hidden" name="appname" value="user_sql"/>
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" id="requesttoken"/>
        <input id="user_sql-save" type="submit" value="<?php p($l->t('Save')); ?>"/>
    </div>
</form>
