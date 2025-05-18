<table>
    <thead>
        <tr>
            <th>Activity Name</th>
            <th>Location</th>
            <th>Date</th>
            <th>Distance</th>
            <th>Difficulty</th>
            <th>Participants</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    <?php if ($data['success']): ?>
        <?php foreach ($data['data'] as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['activity_name']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= (new DateTime($row['date']))->format('F j, Y') ?></td>
                <td><?= htmlspecialchars($row['distance']) ?></td>
                <td><?= htmlspecialchars($row['difficulty']) ?></td>
                <td><?= htmlspecialchars($row['current_participants']) ?>/<?= htmlspecialchars($row['participants']) ?></td>
                <td>
                    <a href="activityDetails.php?id=<?= htmlspecialchars($row['id']) ?>">
                        <button id="join-button">JOIN</button>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7"><?= htmlspecialchars($data['failed_message']) ?></td></tr>
    <?php endif; ?>
    </tbody>
</table>
