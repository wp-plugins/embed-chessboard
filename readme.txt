=== Embed Chessboard ===
Tags: chess, chessboard, pgn, pgn4web
Contributors: pgn4web
Donate link: http://pgn4web.casaschi.net
Requires at least: 2.9
Tested up to: 2.9
Stable tag: trunk

Allows for the insertion of a chessboard displaying chess games within wordpress articles.

== Description ==

Embed Chessboard is a plugin that allows for the insertion of a chessboard displaying chess games within worpress articles.

Use following tag to insert a chessboard:

`[pgn height] chess games in PGN format [/pgn]`

The "height" parameter is the height in pixels of the iframe used to embed the chessboard, if left blank defaults to 600. Enter your chess game (in PGN notation), preview the post and then adjust height as necessary.

Note that HTML tags are stripped from the PGN data, removing all text between "<" and ">". Please make sure your PGN data does not contain "<" and ">" characters.

Example:

`[pgn 500]

[Event "World Championship"]
[Site "Moscow RUS"]
[Date "1985"]
[Round "16"]
[White "Karpov, Anatoly"]
[Black "Kasparov, Garry"]
[Result "0-1"]

1.e4 c5 2.Nf3 e6 3.d4 cxd4 4.Nxd4 Nc6 5.Nb5 d6 6.c4 Nf6 7.N1c3 a6 8.Na3 d5
9.cxd5 exd5 10.exd5 Nb4 11.Be2 Bc5 12.O-O O-O 13.Bf3 Bf5 14.Bg5 Re8 15.Qd2
b5 16.Rad1 Nd3 17.Nab1 h6 18.Bh4 b4 19.Na4 Bd6 20.Bg3 Rc8 21.b3 g5 22.Bxd6
Qxd6 23.g3 Nd7 24.Bg2 Qf6 25.a3 a5 26.axb4 axb4 27.Qa2 Bg6 28.d6 g4 29.Qd2
Kg7 30.f3 Qxd6 31.fxg4 Qd4+ 32.Kh1 Nf6 33.Rf4 Ne4 34.Qxd3 Nf2+ 35.Rxf2 
Bxd3 36.Rfd2 Qe3 37.Rxd3 Rc1 38.Nb2 Qf2 39.Nd2 Rxd1+ 40.Nxd1 Re1+ 0-1

[/pgn]`

Any PGN header tag missing will not be displayed.

The colors of the chessboard plugin can be configured by the site administrator (in order to match the site template) from the Embed Chessboard submenu in the administrator settings menu.

== Installation ==

Reccomended installation method is from the plugins section of the administration pages of your site, serching for the "Embed Chessboard" plugin.

Alternative manual install option:

1. Download the Embed Chessboard plugin package [from the Wordpress plugin directory](http://wordpress.org/extend/plugins/embed-chessboard/) or [from the pgn4web project site](http://code.google.com/p/pgn4web/downloads/list)
1. Unzip
1. Copy to your '/wp-content/plugins' directory
1. Activate plugin

You can find full details of installing a plugin on the [plugin installation page](http://codex.wordpress.org/Managing_Plugins).

== Screenshots ==

1. the chessboard detail
2. full chessboard exmample with chess notation

