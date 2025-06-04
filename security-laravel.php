1)

dont use :: { !! $text !!} => it allows injected js
use ::{{ $text }} =>not allows js

2)Dontmake env public

3) Dont use $reques->all();