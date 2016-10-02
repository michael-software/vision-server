<?php

$jUI->add( new JUI\Heading("TouchImageView - License") );

$jUI->add("Copyright (c) 2012 Michael Ortiz");

$jUI->nline();

$jUI->add("Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the \"Software\"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:");

$jUI->nline();

$jUI->add("The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.");

$jUI->nline();

$jUI->add("THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE");

$jUI->nline();

$link = new JUI\Link("zur Lizenz");
$link->setClick( new JUI\Click( JUI\Click::openUrl, "https://github.com/MikeOrtiz/TouchImageView/blob/master/LICENSE" ) );
$jUI->add( $link );

?>







