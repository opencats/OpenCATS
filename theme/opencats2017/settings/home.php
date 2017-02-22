<?php get_header(); ?>
<div class="content">
            <?php if ($isDemoUser): ?>
                <div class="alert alert-info">
                  <strong>Info!</strong> Note that as a demo user, you do not have privileges to modify any settings.
                </div>
            <?php endif; ?>
                        <div class="hpanel">
                            <div class="panel-heading">
                                Profile
                            </div>
                            <div class="panel-body">

                                <dl class="dl-horizontal">

                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php echo($userID); ?>&amp;privledged=false">
                                        View Profile
                                    </a>
                                </dt>
                                <dd>
                                    View your current profile to verify your information is correct.
                                </dd>


                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=changePassword">
                                        Change Password
                                    </a>
                                </dt>
                                <dd>
                                    Change your CATS login password.
                                </dd>

                            <!--
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=notificationOptions">
                                        Change Notification Options
                                    </a>
                                </dt>
                                <dd>
                                    Change how CATS notifies you of new events.
                                </dd>
                            -->
                        </dl>
</div>
<?php get_footer(); ?>
