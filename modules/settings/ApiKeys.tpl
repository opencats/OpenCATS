<?php /* OpenCATS API Keys Management Template */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: API Keys Management</h2></td>
                </tr>
            </table>

            <p class="note">Create and manage API keys for REST API access (sandbox accounts for developers)</p>

            <?php if (!empty($this->message)): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 4px;">
                <strong>Success:</strong> <?php $this->_($this->message); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($this->error)): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px;">
                <strong>Error:</strong> <?php $this->_($this->error); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($this->newCredentials)): ?>
            <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #856404;">New API Key Created - SAVE THESE NOW!</h3>
                <p><strong>These credentials will only be shown once.</strong></p>
                <table style="background: #fff; padding: 10px; width: 100%;">
                    <tr>
                        <td style="width: 120px;"><strong>API Key:</strong></td>
                        <td><code style="background: #f5f5f5; padding: 5px 10px; font-size: 14px;"><?php $this->_($this->newCredentials['api_key']); ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>API Secret:</strong></td>
                        <td><code style="background: #f5f5f5; padding: 5px 10px; font-size: 14px;"><?php $this->_($this->newCredentials['api_secret']); ?></code></td>
                    </tr>
                </table>
                <p style="margin-bottom: 0; margin-top: 15px;">
                    <strong>Test your API:</strong><br>
                    <code>curl -H "X-Api-Key: <?php $this->_($this->newCredentials['api_key']); ?>" "<?php echo(CATSUtility::getAbsoluteURI()); ?>index.php?m=api&amp;a=ping"</code>
                </p>
            </div>
            <?php endif; ?>

            <?php if (!empty($this->regeneratedSecret)): ?>
            <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #856404;">New Secret Generated - SAVE IT NOW!</h3>
                <p><strong>New API Secret:</strong>
                    <code style="background: #f5f5f5; padding: 5px 10px; font-size: 14px;"><?php $this->_($this->regeneratedSecret); ?></code>
                </p>
                <p style="margin-bottom: 0;">This secret will only be shown once. The old secret no longer works.</p>
            </div>
            <?php endif; ?>

            <br />

            <p class="noteUnsized">Create New API Key</p>

            <form name="createApiKeyForm" id="createApiKeyForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=apiKeys&amp;action=create" method="post">
                <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" style="width: 150px;">
                            <label for="description">Description:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="description" id="description"
                                   placeholder="e.g., JobPulse Development, Testing, Production"
                                   style="width: 400px;" required />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">&nbsp;</td>
                        <td class="tdData">
                            <input type="submit" class="button" value="Create API Key" />
                        </td>
                    </tr>
                </table>
            </form>

            <br />

            <p class="noteUnsized">Existing API Keys</p>

            <?php if (!empty($this->apiKeys) && count($this->apiKeys) > 0): ?>
            <table class="sortable" width="100%">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 280px;">API Key</th>
                        <th>Description</th>
                        <th style="width: 120px;">Owner</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 130px;">Last Used</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->apiKeys as $key): ?>
                    <tr>
                        <td><?php $this->_($key['api_key_id']); ?></td>
                        <td><code style="font-size: 11px;"><?php $this->_($key['api_key']); ?></code></td>
                        <td><?php echo(!empty($key['description']) ? htmlspecialchars($key['description']) : '<em>(No description)</em>'); ?></td>
                        <td><?php $this->_($key['first_name']); ?> <?php $this->_($key['last_name']); ?></td>
                        <td>
                            <?php if ($key['is_active']): ?>
                                <span style="color: green; font-weight: bold;">Active</span>
                            <?php else: ?>
                                <span style="color: red;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($key['last_used'])): ?>
                                <?php $this->_($key['last_used']); ?>
                            <?php else: ?>
                                <em style="color: #999;">Never</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($key['is_active']): ?>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=apiKeys&amp;action=deactivate&amp;keyID=<?php $this->_($key['api_key_id']); ?>"
                                   onclick="return confirm('Deactivate this API key?');"
                                   style="color: orange;">Deactivate</a>
                            <?php else: ?>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=apiKeys&amp;action=activate&amp;keyID=<?php $this->_($key['api_key_id']); ?>"
                                   style="color: green;">Activate</a>
                            <?php endif; ?>
                            |
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=apiKeys&amp;action=regenerate&amp;keyID=<?php $this->_($key['api_key_id']); ?>"
                               onclick="return confirm('Regenerate secret? The old secret will stop working immediately.');"
                               style="color: blue;">New Secret</a>
                            |
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=apiKeys&amp;action=delete&amp;keyID=<?php $this->_($key['api_key_id']); ?>"
                               onclick="return confirm('DELETE this API key permanently? This cannot be undone.');"
                               style="color: red;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p><em>No API keys exist yet. Create one above to get started.</em></p>
            <?php endif; ?>

            <br />

            <p class="noteUnsized">API Quick Reference</p>

            <table class="sortable" width="100%">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th style="width: 80px;">Method</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>?m=api&amp;a=ping</code></td>
                        <td>GET</td>
                        <td>Health check (no auth required)</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=auth</code></td>
                        <td>POST</td>
                        <td>Authenticate and get access token</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=joborders</code></td>
                        <td>GET</td>
                        <td>List all job orders</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=joborders&amp;id=123</code></td>
                        <td>GET</td>
                        <td>Get single job order</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=tearsheets</code></td>
                        <td>GET</td>
                        <td>List all tearsheets</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=tearsheets&amp;id=1&amp;sub=joborders</code></td>
                        <td>GET</td>
                        <td>Get jobs in a tearsheet</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=candidates</code></td>
                        <td>GET</td>
                        <td>List/search candidates</td>
                    </tr>
                    <tr>
                        <td><code>?m=api&amp;a=companies</code></td>
                        <td>GET</td>
                        <td>List/search companies</td>
                    </tr>
                </tbody>
            </table>

            <br />

            <p class="note">Authentication Methods</p>
            <ul>
                <li>Header: <code>X-Api-Key: your-api-key</code> (Recommended)</li>
                <li>Header: <code>Authorization: Bearer your-api-key</code></li>
                <li>Query parameter: <code>?api_key=your-api-key</code> (Less secure)</li>
            </ul>

        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
