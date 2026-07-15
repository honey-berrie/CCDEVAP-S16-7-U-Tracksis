

async function updateGroupData() {
    const groupName = document.getElementById('groupName').value.trim();
    const thesisTitle = document.getElementById('thesisTitle').value.trim();
    const abstract = document.getElementById('abstract').value.trim();

    if (!groupName || !thesisTitle || !abstract) {
        alert('Please fill in all fields.');
        return;
    }

    try {
        const res = await fetch(`../../api/update_user_group.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ group_name: groupName, thesis_title: thesisTitle, abstract }),
        });
        const data = await res.json();

        if (!res.ok) {
            alert('Failed to update group data: ' + (data.error || res.statusText));
            return;
        }

        alert('Group data updated successfully!');
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

async function fetchGroupData() {

    const noGroupMessage = document.getElementById('noGroupMessage');
    const groupProfileContent = document.getElementById('groupProfileContent');

    const groupNameElement = document.getElementById('groupName');
    const thesisTitleElement = document.getElementById('thesisTitle');
    const abstractElement = document.getElementById('abstract');
    const adviserNameElement = document.getElementById('adviserName');


    try {

        const res = await fetch(`../../api/get_user_group.php`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        const data = await res.json();

        if (!res.ok) {
            alert('Failed to fetch group data: ' + res.statusText);
            return;
        }

        const group = data.group;
        const groupMembers = data.members || [];
        const adviser = data.adviser || null;

        if (group) {
            groupNameElement.value = group.group_name;
            thesisTitleElement.value = group.thesis_title;
            abstractElement.value = group.abstract;

            // display members
            const membersBox = document.getElementById('membersBox');
            const memberTemplate = document.getElementById('memberTemplate');

            // clear existing members except the template
            membersBox.querySelectorAll('.member-item').forEach(item => {
                item.remove();
            });

            groupMembers.forEach(member => {

                var memberItem = document.createElement('div');
                memberItem.className = 'member-item';

                memberItem.innerHTML = `
                    <div class="member-avatar">${member.initials}</div>
                    <div class="member-info">
                        <p class="member-name">${member.firstname + ' ' + member.lastname}</p>
                        <p class="member-role">${String(member.role).charAt(0).toUpperCase() + String(member.role).slice(1)}</p>
                    </div>
                `;

                membersBox.appendChild(memberItem);
            });

            // display adviser
            console.log('Adviser:', adviser);
            if (adviser) {

                var adviserBox = document.getElementById('adviserBox');

                adviserBox.querySelectorAll('.member-item').forEach(item => {
                    item.remove();
                });

                var adviserItem = document.createElement('div');
                adviserItem.className = 'member-item';

                adviserItem.innerHTML = `
                    <div class="member-avatar">${adviser.initials}</div>
                    <div class="member-info">
                        <p class="member-name">${adviser.firstname + ' ' + adviser.lastname}</p>
                        <p class="member-role">${String(adviser.role).charAt(0).toUpperCase() + String(adviser.role).slice(1)}</p>
                    </div>
                `;

                adviserBox.appendChild(adviserItem);
            }

            groupProfileContent.style.display = 'flex';
        } else {
            noGroupMessage.style.display = 'block';
        }

    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', fetchGroupData);