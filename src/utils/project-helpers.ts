import projects from '../data/projects.json';

/**
 * Sắp xếp các dự án theo ngày hoàn thành từ mới nhất đến cũ nhất.
 * @returns Một mảng các dự án đã được sắp xếp.
 */
export function getSortedProjects() {
  return projects.sort((a, b) => new Date(b.info.completionDate).getTime() - new Date(a.info.completionDate).getTime());
}

