<template>
    <ul class="meet__date__list mb-50d-f fd-c">
        <li 
            v-for="day, indexDay in meet.dates"
            class="mb-25" 
        >
            <h3 class="mb-10 fs-m">{{ day }}</h3>

            <table>
                <tr>
                    <th></th>
                    <th
                        v-for="interval in getIntervalMeet(meet.start, meet.end, meet.interval)" 
                        class="py-10 px-20 ta-c"
                    >
                        {{ interval[0] }}
                        <br>-<br>
                        {{ interval[1] }}
                    </th>
                </tr>
                <tr 
                    v-for="user, login in meet.availables"
                >
                    <th class="d-f ai-c p-s l-0 bc-w fw-s py-10 pr-20 ta-l">
                        <p>
                            {{ login }}
                            <span 
                                v-if="user.id == meet.leader.id"
                                class="mark mark-info ml-5"
                            >
                                лидер
                            </span>
                            <span
                                v-if="userID == user.id && userID != meet.leader.id"
                                class="mark mark-info ml-5"
                            >
                                вы
                            </span>
                        </p>
                        <a 
                            v-if="!onlyView && userID == meet.leader.id && userID != user.id"
                            @click.prevent="fetchDeleteUser(user.id)"
                            href="#" 
                            class="d-f jc-c ai-c ml-a"
                        >
                            ➖
                        </a>
                    </th>
                    <td
                        v-if="userID != user.id || meet.block || onlyView"
                        v-for="available in user.availables[indexDay]"
                        :class="{ meet__active : available, meet__disactive: !available }"
                        class="bg-d ta-c p-r"
                    >
                        <div class="p-a t-0 l-0 w-100 h-100 d-f jc-c ai-c">
                            <p>{{ available ? "✔" : "❌" }}</p>
                        </div>
                    </td>
                    <td
                        v-else-if="userID == user.id && !onlyView && !meet.block"
                        v-for="available, indexTime in user.availables[indexDay]"
                        @click="updateUserInterval(login, indexDay, indexTime, !available)"
                        :class="{ available : available }"
                        class="bg-d ta-c p-r h-p btn-meet" 
                    >
                        <div
                            v-if="available"
                            class="p-a t-0 l-0 w-100 h-100 d-f jc-c ai-c"
                        >
                            <p>✔</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="fw-s py-10 pr-20 ta-l">
                        <p>Доступность</p>
                    </th>
                    <td
                        v-for="count in getAllAvailablesInInterval(indexDay)"
                        :class="{ meet__active: count == getMaxElement(getAllAvailablesInInterval(indexDay)) }"
                        class="bg-d ta-c p-r py-10 px-20 fw-n"
                    >
                        {{ count }}
                    </td>
                </tr>
            </table>
        </li>
    </ul>
</template>


<script>
export default {
    name: 'availablesMeet',
    props: {
        meet: {
            type: Object,
            required: true,
        },
        userID: {
            required: false,
        },
        onlyView: {
            type: Boolean,
            required: false,
            default: false,
        }
    },
    methods: {
        getIntervalMeet(startTime, endTime, interval) {
            let [startHour, startMinute] = startTime.split(":").map(Number);
            let [endHour, endMinute] = endTime.split(":").map(Number);

            if (startHour > endHour - 1) {
                return [];
            }

            let resultTime = [];

            while (startHour !== endHour || startMinute !== endMinute) {
                let result = [ // ["09:00"]
                    `${String(startHour).padStart(2, '0')}:${String(startMinute).padStart(2, '0')}`,
                ];

                startMinute += interval;

                if (startMinute > 59) {
                    startMinute = 0;
                    startHour++;
                }

                result.push(`${String(startHour).padStart(2, '0')}:${String(startMinute).padStart(2, '0')}`); // ["09:00", "10:00"]
                resultTime.push(result);
            }

            return resultTime;
        },
        getAllAvailablesInInterval(indexDay) {
            let result = [];

            for (let user in this.meet.availables) {
                this.meet.availables[user].availables[indexDay].forEach((available, index) => {
                    if (!result[index]) {
                        result[index] = available;
                    } else {
                        result[index] += available;
                    }
                });
            }

            return result;
        },
        getMaxElement(array) {
            let result = array[0];

            array.forEach(el => {
                if (el > result) {
                    result = el;
                }
            });

            return result;
        },
        updateUserInterval(login, day, time, value) {
            this.meet.availables[login].availables[day][time] = Number(value);
            this.fetchUpdateInterval(this.meet.availables[login].availables);
        },
        async fetchDeleteUser(userID) {
            try {
                let requestOptions = {
                    method: 'DELETE',
                }

                let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}/${this.$route.params.hashLeader}/user/${userID}`, requestOptions);

                if (response.status > 199 && response.status < 300) {
                    this.$emit('update');
                } else {
                    let data = await response.json();
                    throw Error(JSON.stringify(data.error));
                }
            } catch (e) {
                console.log(JSON.parse(e.message));
            }
        },
        async fetchUpdateInterval(availables) {
            try {
                let requestOptions = {
                    method: 'PATCH',
                    body: JSON.stringify(availables),
                    headers: {
                        'Content-Type': 'application/json',
                    },
                }
                let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}/user/${this.userID}`, requestOptions);

                if (response.status > 199 && response.status < 300) {
                    this.$emit('update');
                } else {
                    let data = await response.json();
                    throw Error(JSON.stringify(data.error));
                }
            } catch (e) {
                console.log(JSON.parse(e.message));
            }
        }
    }
}
</script>


<style>

</style>