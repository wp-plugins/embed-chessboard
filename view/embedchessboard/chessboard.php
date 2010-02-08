<div class="chessboard-wrapper">
  <textarea id="<?php echo $pgnId ?>" style="display:none;"><?php echo $pgnText ?></textarea>
  <iframe src='<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/embed-chessboard/pgn4web/board.html?am=l&d=3000&ss=26&ps=d&pf=d&lcs=YKqR&dcs=Td2v&bbcs=Td2v&hm=b&hcs=FVJG&bd=c&cbcs=Xe2L&ctcs=plAE&hd=j&md=j&tm=13&fhcs=$$$$&fhs=80p&fmcs=$$$$&fccs=v71$&hmcs=Td2v&fms=80p&fcs=m&cd=i&bcs=____&fp=13&hl=f&fh=<?php echo $height ?>&fw=p&pi=<?php echo $pgnId ?>' frameborder=0 width=100% height=<?php echo $height ?> scrolling='no' marginheight='0' marginwidth='0'>sorry, you'd need iframe support in your browser</iframe>
</div>

