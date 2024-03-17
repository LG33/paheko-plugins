<nav class="tabs">
	{if $current === 'config'}
	<aside>
		{linkbutton href="import.php" shape="import" label="Import de tâches"}
	</aside>
	{/if}
	<ul>
	{if $logged_user.id}
		<li{if $current === 'index'} class="current"{/if}><a href="./">Ma semaine</a></li>
		<li{if $current === 'year'} class="current"{/if}><a href="year.php">Mon résumé</a></li>
	{/if}
{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
		<li{if $current === 'all'} class="current"{/if}><a href="all.php">Suivi</a></li>
		<li{if $current === 'stats'} class="current"{/if}><a href="stats.php">Statistiques</a></li>
{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
		<li{if $current === 'report'} class="current"{/if}><a href="report.php">Valoriser</a></li>
{/if}
		<li{if $current === 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
{/if}
	</ul>
</nav>