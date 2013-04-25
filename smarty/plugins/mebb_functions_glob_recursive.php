<?php
/**
 * ------------------------------------------------------------------------- *
 * This library is free software; you can redistribute it and/or             *
 * modify it under the terms of the GNU Lesser General Public                *
 * License as published by the Free Software Foundation; either              *
 * version 2.1 of the License, or (at your option) any later version.        *
 *                                                                           *
 * This library is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         *
 * Lesser General Public License for more details.                           *
 *                                                                           *
 * You should have received a copy of the GNU Lesser General Public          *
 * License along with this library; if not, write to the Free Software       *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA *
 * ------------------------------------------------------------------------- *
 *
 * @package mebb-lib-functions
 * @author Karlheinz Toni <karlheinz.toni@gmail.com>
 * @link http://code.google.com/p/smarty-gettext/
 * @copyright 2012 Karlheinz Toni
 */

namespace mebb\lib\functions;

if(!function_exists('\\mebb\lib\\functions\\glob_recursive')){
  // Does not support flag GLOB_BRACE
  function glob_recursive($pattern, $flags = 0){
    $files = glob($pattern, $flags);
        
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
      $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
        
    return $files;
  }
}

?>
