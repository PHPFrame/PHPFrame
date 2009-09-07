<?php if (isset($data["customers"]) && $data["customers"] instanceof PHPFrame_DomainObjectCollection) : ?>
<table>
<tr>
    <th>First name:</th>
    <th>Last name:</th>
    <th>Email name:</th>
</tr>
<?php foreach ($data["customers"] as $customer) : ?>
<tr>
    <td><?php echo $customer->getFirstName(); ?></td>
    <td><?php echo $customer->getLastName(); ?></td>
    <td><?php echo $customer->getEmail(); ?></td>
</tr> 
<?php endforeach; ?>
</table>
<?php endif; ?>