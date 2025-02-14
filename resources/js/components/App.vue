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
        <div v-for="(column, index) in columns" :key="index" class="w-72 bg-gray-200 rounded-lg p-4" @drop="drop(column)" @dragover.prevent>
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

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const columns = ref([
  { title: 'To Do', status: 'todo', tasks: [] },
  { title: 'In Progress', status: 'in_progress', tasks: [] },
  { title: 'Completed', status: 'completed', tasks: [] }
]);

const modal = ref({ show: false, type: '', column: null, index: null, task: {}, title: '' });
const draggedTask = ref(null);
const sourceColumn = ref(null);
const sourceIndex = ref(null);

// 🟡 1. Obter todas as tarefas
async function getTasks() {
  try {
    const { data } = await axios.get('/api/tasks');
    columns.value = [
      { title: 'To Do', status: 'todo', tasks: data.filter(task => task.status === 'todo') },
      { title: 'In Progress', status: 'in_progress', tasks: data.filter(task => task.status === 'in_progress') },
      { title: 'Completed', status: 'completed', tasks: data.filter(task => task.status === 'completed') }
    ];
  } catch (error) {
    console.error('Erro ao obter tarefas:', error);
  }
}

// 🟢 2. Criar nova tarefa
async function createTask(task) {
  try {
    // Adiciona o status 'todo' à tarefa antes de enviá-la
    const newTask = { ...task, status: 'todo' };
    const { data } = await axios.post('/api/tasks', newTask);
    columns.value[0].tasks.unshift(data); // Adiciona automaticamente à coluna 'To Do'
  } catch (error) {
    alert('Erro ao criar tarefa: ' + error.response?.data?.message || error.message);
  }
}

// 🟠 3. Editar tarefa
async function editTask(task) {
  try {
    const { data } = await axios.put(`/api/tasks/${task.id}`, task);
    const column = columns.value.find(col => col.status === data.status);
    const index = column.tasks.findIndex(t => t.id === data.id);
    column.tasks[index] = data;
  } catch (error) {
    console.error('Erro ao editar tarefa:', error);
  }
}

// 🔴 4. Deletar tarefa
async function deleteTask(id) {
  try {
    await axios.delete(`/api/tasks/${id}`);
    columns.value.forEach(col => {
      col.tasks = col.tasks.filter(task => task.id !== id);
    });
  } catch (error) {
    console.error('Erro ao deletar tarefa:', error);
  }
}

// 🟣 5. Mover tarefa (Drag-and-Drop)
async function moveTask(task, targetColumn) {
  try {
    const { data } = await axios.put(`/api/tasks/${task.id}`, { status: targetColumn.status });
    getTasks(); // Atualiza colunas com dados do backend
  } catch (error) {
    console.error('Erro ao mover tarefa:', error);
  }
}

// 📝 Modal de CRUD
function openModal(type, column = null, index = null) {
  modal.value = {
    show: true,
    type,
    column,
    index,
    task: { ...column?.tasks[index] } || {},
    title: type === 'add' ? 'Add Task' : type === 'edit' ? 'Edit Task' : 'Delete Task'
  };
}

async function confirmAction() {
  const { type, column, index, task } = modal.value;
  if (type === 'add') {
    await createTask(task);
  } else if (type === 'edit') {
    await editTask(task);
  } else if (type === 'delete') {
    await deleteTask(task.id);
  }
  modal.value.show = false;
  getTasks();
}

// Drag and Drop
function drag(task, column, index) {
  draggedTask.value = task;
  sourceColumn.value = column;
  sourceIndex.value = index;
}

async function drop(targetColumn) {
  console.log('Target Column:', targetColumn);
  if (draggedTask.value && sourceColumn.value !== targetColumn) {
    sourceColumn.value.tasks.splice(sourceIndex.value, 1);
    
    if (targetColumn && targetColumn.tasks) {
      targetColumn.tasks.push(draggedTask.value);
      await moveTask(draggedTask.value, targetColumn);
    } else {
      console.error('Target column is undefined or does not have tasks');
    }
    
    draggedTask.value = null;
    sourceColumn.value = null;
    sourceIndex.value = null;
  }
}

onMounted(() => {
  getTasks();
});
</script>


<style scoped>
h1 {
  margin-bottom: 1rem;
}
.new-task {
  margin-bottom: 1rem;
}
s {
  color: #888;
}
</style>
