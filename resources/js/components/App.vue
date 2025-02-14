<template>
  <div class="flex min-h-screen bg-gray-100 p-5">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-4 rounded-lg">
      <h2 class="text-lg font-bold text-gray-800 mb-4">Task Manager</h2>
      <ul class="space-y-2">
        <li><a href="#" class="block px-3 py-2 text-indigo-600 font-semibold bg-indigo-100 rounded-md">Dashboard</a></li>
        <li><a href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-200 rounded-md">My Tasks</a></li>
        <li><a href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-200 rounded-md">Settings</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Project Board</h1>
        <button @click="openModal('add')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">Add Task</button>
      </div>

      <div class="flex space-x-4 overflow-auto" @dragover.prevent>
        <div v-for="(column, index) in columns" :key="index" class="w-72 bg-gray-200 rounded-lg p-4" @drop="drop($event, column)" @dragover.prevent>
          <h3 class="text-lg font-bold mb-3">{{ column.title }}</h3>
          <div class="space-y-3">
            <div v-for="(task, tIndex) in column.tasks" :key="task.id" draggable="true" @dragstart="drag(task, column, tIndex)" class="bg-white p-3 rounded-md shadow-sm border border-gray-300">
              <p class="font-semibold">{{ task.title }}</p>
              <p class="text-sm text-gray-600">{{ task.description }}</p>
              <div class="flex justify-between mt-2">
                <button @click="openModal('edit', column, tIndex)" class="text-blue-500 text-xs">Edit</button>
                <button @click="openModal('delete', column, tIndex)" class="text-red-500 text-xs">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal -->
    <div v-if="modal.show" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
      <div class="bg-white p-6 rounded-lg w-96">
        <h2 class="text-lg font-bold mb-4">{{ modal.title }}</h2>
        <div v-if="modal.type !== 'delete'">
          <input v-model="modal.task.title" placeholder="Task Title" class="w-full mb-3 p-2 border rounded" />
          <textarea v-model="modal.task.description" placeholder="Task Description" class="w-full mb-3 p-2 border rounded"></textarea>
        </div>
        <p v-else class="mb-3">Are you sure you want to delete this task?</p>
        <div class="flex justify-end space-x-2">
          <button @click="confirmAction" class="px-4 py-2 bg-indigo-600 text-white rounded">Confirm</button>
          <button @click="modal.show = false" class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      draggedTask: null,
      sourceColumn: null,
      sourceIndex: null,
      columns: [
        { title: 'To Do', tasks: [] },
        { title: 'In Progress', tasks: [] },
        { title: 'Completed', tasks: [] }
      ],
      modal: { show: false, type: '', column: null, index: null, task: {}, title: '' }
    };
  },
  methods: {
    drag(task, column, index) {
      this.draggedTask = task;
      this.sourceColumn = column;
      this.sourceIndex = index;
    },
    drop(event, targetColumn) {
      if (this.draggedTask && this.sourceColumn !== targetColumn) {
        this.sourceColumn.tasks.splice(this.sourceIndex, 1);
        targetColumn.tasks.push(this.draggedTask);
        this.draggedTask = null;
        this.sourceColumn = null;
        this.sourceIndex = null;
      }
    },
    openModal(type, column = null, index = null) {
      this.modal = {
        show: true,
        type,
        column,
        index,
        task: { ...column?.tasks[index] } || {},
        title: type === 'add' ? 'Add Task' : type === 'edit' ? 'Edit Task' : 'Delete Task'
      };
    },
    confirmAction() {
      const { type, column, index, task } = this.modal;
      if (type === 'add') {
        this.columns[0].tasks.push({ id: Date.now(), ...task });
      } else if (type === 'edit') {
        Object.assign(column.tasks[index], task);
      } else if (type === 'delete') {
        column.tasks.splice(index, 1);
      }
      this.modal.show = false;
    }
  }
};
</script>
