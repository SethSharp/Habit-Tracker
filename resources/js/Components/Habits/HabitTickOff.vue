<script setup>
import JSConfetti from 'js-confetti'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    habits: Object,
})

const isAllCompleted = () => {
    for (let i = 0; i < props.habits.length; i++) {
        if (!props.habits[i].completed) {
            return
        }
    }
    jsConfetti.addConfetti()
}

const completeHabit = (habit) => {
    router.post(
        route('schedule.complete', habit.id),
        {},
        {
            onSuccess: () => {
                habit.completed = true
                isAllCompleted()
            },
        }
    )
}

const cancelHabit = (habit) => {
    router.post(
        route('schedule.cancel', habit.id),
        {},
        {
            onSuccess: () => {
                habit.completed = false
            },
        }
    )
}

const jsConfetti = new JSConfetti()
</script>

<template>
    <div class="flex-wrap w-full">
        <div v-for="scheduledHabit in habits">
            <div
                v-if="!scheduledHabit.completed"
                class="flex my-2 p-2 justify-between rounded border"
            >
                <p class="text-md my-auto">
                    {{ scheduledHabit.habit.name }}
                </p>

                <div class="justify-end flex gap-x-2">
                    <div class="cursor-pointer ml-2 flex overflow-hidden w-auto">
                        <div
                            @click="completeHabit(scheduledHabit)"
                            class="w-fit p-2 !text-green-500 transition"
                        >
                            complete
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-for="scheduledHabit in habits">
            <div
                v-if="scheduledHabit.completed"
                class="flex my-2 p-2 justify-between rounded border bg-green-200 border-green-400"
            >
                <p class="text-md my-auto">
                    {{ scheduledHabit.habit.name }}
                </p>

                <div class="justify-end flex gap-x-2">
                    <div class="cursor-pointer ml-2 flex overflow-hidden w-auto">
                        <div @click="cancelHabit(scheduledHabit)" class="w-fit p-2 transition">
                            cancel
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
