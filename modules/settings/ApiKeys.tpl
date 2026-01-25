{* OpenCATS API Keys Management Template *}
{* File: modules/settings/ApiKeys.tpl *}

{include file="./modules/settings/Header.tpl" title="API Keys Management"}

<div id="contents">
    <table>
        <tr>
            <td width="3%">&nbsp;</td>
            <td width="94%">
            
                <h2>API Keys Management (Sandbox Accounts)</h2>
                <p>Create and manage API keys for REST API access. These function like "sandbox accounts" for developers.</p>
                
                {* Success/Error Messages *}
                {if $message}
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 4px;">
                    <strong>✓</strong> {$message|escape}
                </div>
                {/if}
                
                {if $error}
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px;">
                    <strong>✗</strong> {$error|escape}
                </div>
                {/if}
                
                {* Display New Credentials (One Time Only!) *}
                {if $newCredentials}
                <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;">
                    <h3 style="margin-top: 0; color: #856404;">⚠️ New API Key Created - SAVE THESE NOW!</h3>
                    <p><strong>These credentials will only be shown once.</strong></p>
                    <table style="background: #fff; padding: 10px; width: 100%;">
                        <tr>
                            <td style="width: 120px;"><strong>API Key:</strong></td>
                            <td><code style="background: #f5f5f5; padding: 5px 10px; font-size: 14px;">{$newCredentials.api_key|escape}</code></td>
                        </tr>
                        <tr>
                            <td><strong>API Secret:</strong></td>
                            <td><code style="background: #f5f5f5; padding: 5px 10px; font-size: 14px;">{$newCredentials.api_secret|escape}</code></td>
                        </tr>
                    </table>
                    <p style="margin-bottom: 0; margin-top: 15px;">
                        <strong>Test your API:</strong><br>
                        <code>curl -X POST "{$newCredentials.base_url|default:'http://your-opencats-url'}/index.php?m=api&a=auth" \<br>
                        &nbsp;&nbsp;-H "Content-Type: application/json" \<br>
                        &nbsp;&nbsp;-d '{literal}{"api_key": "{/literal}{$newCredentials.api_key|escape}{literal}", "api_secret": "{/literal}{$newCredentials.api_secret|escape}{literal}"}{/literal}'</code>
                    </p>
                </div>
                {/if}
                
                {* Display Regenerated Secret *}
                {if $regeneratedSecret}
                <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;">
                    <h3 style="margin-top: 0; color: #856404;">⚠️ New Secret Generated - SAVE IT NOW!</h3>
                    <p><strong>New API Secret:</strong> 
                        <code style="background: #f5f5f5; padding: 5px 10px; font-size: 14px;">{$regeneratedSecret|escape}</code>
                    </p>
                    <p style="margin-bottom: 0;">This secret will only be shown once. The old secret no longer works.</p>
                </div>
                {/if}
                
                <hr style="margin: 30px 0;">
                
                {* Create New API Key Form *}
                <h3>Create New API Key</h3>
                <form method="post" action="index.php?m=settings&a=apiKeys&action=create">
                    <table>
                        <tr>
                            <td style="width: 120px;"><label for="description">Description:</label></td>
                            <td>
                                <input type="text" name="description" id="description" 
                                       placeholder="e.g., JobPulse Development, Testing, Production"
                                       style="width: 400px; padding: 8px;" required>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="submit" value="Create API Key" class="button" 
                                       style="margin-top: 10px; padding: 10px 20px;">
                            </td>
                        </tr>
                    </table>
                </form>
                
                <hr style="margin: 30px 0;">
                
                {* List All API Keys *}
                <h3>Existing API Keys</h3>
                
                {if $apiKeys|@count > 0}
                <table class="sortable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 280px;">API Key</th>
                            <th>Description</th>
                            <th style="width: 120px;">Owner</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 140px;">Last Used</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$apiKeys item=key}
                        <tr>
                            <td>{$key.api_key_id}</td>
                            <td><code style="font-size: 11px;">{$key.api_key|escape}</code></td>
                            <td>{$key.description|escape|default:'(No description)'}</td>
                            <td>{$key.first_name|escape} {$key.last_name|escape}</td>
                            <td>
                                {if $key.is_active}
                                    <span style="color: green; font-weight: bold;">● Active</span>
                                {else}
                                    <span style="color: red;">○ Inactive</span>
                                {/if}
                            </td>
                            <td>
                                {if $key.last_used}
                                    {$key.last_used|date_format:"%Y-%m-%d %H:%M"}
                                {else}
                                    <em style="color: #999;">Never</em>
                                {/if}
                            </td>
                            <td>
                                {if $key.is_active}
                                    <a href="index.php?m=settings&a=apiKeys&action=deactivate&keyID={$key.api_key_id}"
                                       onclick="return confirm('Deactivate this API key?');"
                                       style="color: orange;">Deactivate</a>
                                {else}
                                    <a href="index.php?m=settings&a=apiKeys&action=activate&keyID={$key.api_key_id}"
                                       style="color: green;">Activate</a>
                                {/if}
                                | 
                                <a href="index.php?m=settings&a=apiKeys&action=regenerate&keyID={$key.api_key_id}"
                                   onclick="return confirm('Regenerate secret? The old secret will stop working immediately.');"
                                   style="color: blue;">New Secret</a>
                                |
                                <a href="index.php?m=settings&a=apiKeys&action=delete&keyID={$key.api_key_id}"
                                   onclick="return confirm('DELETE this API key permanently? This cannot be undone.');"
                                   style="color: red;">Delete</a>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                {else}
                <p><em>No API keys exist yet. Create one above to get started.</em></p>
                {/if}
                
                <hr style="margin: 30px 0;">
                
                {* API Documentation Quick Reference *}
                <h3>API Quick Reference</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="background: #f5f5f5;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Endpoint</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Method</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Description</th>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>/index.php?m=api&a=auth</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">POST</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Authenticate and get access token</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>/index.php?m=api&a=joborders</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">GET</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">List all job orders</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>/index.php?m=api&a=joborders&id=123</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">GET</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Get single job order</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>/index.php?m=api&a=tearsheets</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">GET</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">List all tearsheets</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>/index.php?m=api&a=tearsheets&id=1&sub=joborders</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">GET</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Get jobs in a tearsheet</td>
                    </tr>
                </table>
                
                <p style="margin-top: 20px;">
                    <strong>Authentication:</strong> Include the API key in requests using one of these methods:
                </p>
                <ul>
                    <li>Header: <code>X-Api-Key: your-api-key</code></li>
                    <li>Header: <code>Authorization: Bearer your-api-key</code></li>
                    <li>Query parameter: <code>?api_key=your-api-key</code> (less secure)</li>
                </ul>
                
            </td>
            <td width="3%">&nbsp;</td>
        </tr>
    </table>
</div>

{include file="./modules/settings/Footer.tpl"}
