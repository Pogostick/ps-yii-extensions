<ul>
	<li><?php echo PS::link( 'Approve Comments', array( 'comment/list' ) ) . ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
	<li><?php echo PS::link( 'Create New Post', array( 'post/create' ) ); ?></li>
	<li><?php echo PS::link( 'Manage Posts', array( 'post/admin' ) ); ?></li>
	<li><?php echo PS::linkButton( 'Logout', array( 'submit' => '', 'params' => array( 'command' => 'logout' ) ) ); ?></li>
</ul>