=== Embed Chessboard ===
Tags: chess, chessboard, pgn, pgn4web, embed, page, post, plugin
Contributors: pgn4web
Donate link: http://pgn4web.casaschi.net
Requires at least: 2.9
Tested up to: 2.9
Stable tag: trunk

Allows the insertion of code to display chess games within an iframe

== Description ==

Embed Chessboard is a plugin that will let you embed a chessboard showing games provided in PGN format.

== Installation ==

1. Download EmbedChessboard plugin [from the Wordpress plugin directory](http://wordpress.org/extend/plugins/embed-chessboard/) or [from the pgn4web project site](http://code.google.com/p/pgn4web/downloads/list)
1. Unzip
1. Copy to your '/wp-content/plugins' directory
1. Activate plugin

You can find full details of installing a plugin on the [plugin installation page](http://codex.wordpress.org/Managing_Plugins)

== Usage ==

Use following tag to insert a chessboard:

`[pgn height] ... chess game notation in PGN format ... [/pgn]`

The "height" parameter is the height in pixels of the iframe used to embed the chessboard, if left blank defaults to 600. Enter your chess game (in PGN notation), preview the post and then adjust height as necessary.

Note that HTML tags are stripped from the PGN data, removing all text between "<" and ">". Please make sure your PGN data does not contain "<" and ">" characters.

Example:

`[pgn 500]

[White "W. Hartston"] 
[Black "S. Mariotti"] 
[Result "0-1"] 
 
1.e4 e5 2.Nc3 Nc6 3.g3 Bc5 4.Bg2 h5 5.Nf3 h4 6.Nxh4 Rxh4
7.gxh4 Qxh4 8.d4 Bxd4 9.Qe2 Bxc3+ 10.bxc3 d6 11.O-O g5 12.Qe3 f6 
13.Qg3 Qh7 14.Bf3 Nge7 15.Re1 Ng6 16.Be3 Ke7 17.c4 b6 18.c5 bxc5 
19.c3 Nf4 20.Bg4 Bxg4 21.Qxg4 Rh8 22.h4 Qg8 23.Bxf4 Rxh4 24.Qg2 exf4 
25.e5 Nxe5 26.Qb7 Rg4+ 27.Kf1 Qc4+ 28.Re2 Kd7 29.Qe4 f3 30.Qxc4 Nxc4 31.Rc2 Rg2 
32.Re1 f5 33.Rce2 Kc6 

[/pgn]`

The colors of the chessboard plugin can be configured by the site administrator (in order to match the site template) from the Embed Chessboard submenu in the administrator settings.

[http://pgn4web-test-wp.casaschi.net](http://pgn4web-test-wp.casaschi.net) shows an application example of the embed-chessboard plugin.

