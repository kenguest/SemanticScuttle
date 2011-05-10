<h3><?php echo T_('SSL client certificates'); ?></h3>
<?php if (count($sslClientCerts)) { ?>
<table>
 <thead>
  <tr>
   <th>Options</th>
   <th><?php echo T_('Serial'); ?></th>
   <th><?php echo T_('Name'); ?></th>
   <th><?php echo T_('Email'); ?></th>
   <th><?php echo T_('Issuer'); ?></th>
  </tr>
 </thead>
 <tbody>
 <?php foreach($sslClientCerts as $cert) { ?>
  <tr <?php if ($cert->isCurrent()) { echo 'class="ssl-current"'; } ?>>
   <td><a href="#FIXME">delete</a></td>
   <td><?php echo htmlspecialchars($cert->sslSerial); ?></td>
   <td><?php echo htmlspecialchars($cert->sslName); ?></td>
   <td><?php echo htmlspecialchars($cert->sslEmail); ?></td>
   <td><?php echo htmlspecialchars($cert->sslClientIssuerDn); ?></td>
  </tr>
 <?php } ?>
 </tbody>
</table>
<?php } else { ?>
 <p><?php echo T_('No certificates registered'); ?></p>
<?php } ?>

<?php if ($currentCert) { ?>
 <?php if ($currentCert->isRegistered($sslClientCerts)) { ?>
  <p><?php echo T_('Your current certificate is already registered with your account.'); ?></p>
 <?php } else { ?>
  <p>
   <a href="#FIXME">
    <?php echo T_('Register current certificate to automatically login.'); ?>
   </a>
  </p>
 <?php } ?>
<?php } else { ?>
 <p><?php echo T_('Your browser does not provide a certificate.'); ?></p>
<?php } ?>