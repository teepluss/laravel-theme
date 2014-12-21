<?php namespace Teepluss\Theme;

class UnknownThemeException extends \UnexpectedValueException {}
class UnknownViewFileException extends \UnexpectedValueException {}
class UnknownLayoutFileException extends \UnexpectedValueException {}
class UnknownWidgetFileException extends \UnexpectedValueException {}
class UnknownWidgetClassException extends \UnexpectedValueException {}
class UnknownPartialFileException extends \UnexpectedValueException {}

class JSMin_UnterminatedRegExpException extends \Exception {}
class JSMin_UnterminatedStringException extends  \Exception {}
class JSMin_UnterminatedCommentException extends  \Exception {}