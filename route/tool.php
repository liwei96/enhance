<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/15
 * Time: 14:42
 */

use think\facade\Route;

Route::rule('tool/email','api/email/send');

Route::rule('option/sources','api/option/sources');